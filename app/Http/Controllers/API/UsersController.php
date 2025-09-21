<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\UserDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Http\Request;

class UsersController extends BaseController
{
    public function index()
    {
        // Return users with their canonical details for the frontend user list
        $users = User::leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
            ->leftJoin('roles', 'users.roles_id', '=', 'roles.id')
            ->where(function($q) {
                $q->whereNull('roles.name')->orWhere('roles.name', '!=', 'Admin');
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'roles.name as role',
                'user_details.department as department',
                'user_details.contact_number as contact_number',
                'user_details.plate_number as plate_number',
                'user_details.or_path as or_path',
                'user_details.cr_path as cr_path',
                'user_details.from_pending as from_pending'
            )
                ->selectRaw('(SELECT COUNT(*) FROM vehicles WHERE vehicles.user_id = users.id) as vehicle_count')
            ->get();

        return $this->sendResponse($users, "Users retrieved successfully.");
    }

    public function show(User $user)
    {
        return $this->sendResponse($user, "User retrieved successfully.");
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'firstname' => 'required',
            'middlename' => 'nullable',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email',
            'referrer' => [
                'nullable', 
                'email', 
                Rule::exists('users', 'email'), 
                function (string $attribute, mixed $value, \Closure $fail) {
                    $referrer = User::where('email', $value)->first();
                    if ($referrer && $referrer->refer_count >= 10) { $fail("Referrer exceeded count of refers."); }
                }
            ],
            'role' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validator Error.', $validator->errors());
        }

        try {
            $user = DB::transaction(function () use ($input) {
                $user = User::create([
                    'roles_id' => $input['role'],
                    'name' => $input['firstname'] . " " . $input['lastname'],
                    'email' => $input['email'],
                    'password' => Hash::make(Str::random(16)),
                ]);
        
                $user->userDetail()->create([
                    'firstname' => $input['firstname'],
                    'lastname' => $input['lastname'],
                    'middleinitial' => strtoupper(substr($input['middlename'], 0, 1))
                ]);

                if (in_array($user->role()->first()->name, ['Member', 'Team Leader'])) {
                    $team = $user->myTeam()->create([
                        'user_id' => $user->id,
                        'name' => $user->name . "'s TEAM",
                        'personal_team' => 1,
                    ]);
            
                    $team->team_user()->create([
                        'user_id' => $user->id,
                        'created_at' => Carbon::now()
                    ]);
                    
                    if (!is_null($input['referrer']) && $user->role->name == 'Member') {
                        $referrer = User::where('email', $input['referrer'])->first();
                        $referrer->refer_count = $referrer->refer_count + 1;
                        $referrer->save();
            
                        $team->team_user()->update([
                            'user_id' => $user->id,
                            'lead_id' => $referrer->id,
                        ]);
                    }
                }

                return $user;
            });

            return $this->sendResponse([
                "name" => $user->name,
            ], "You've created {$user->name} as a {$user->role->name} successfully.");
        } catch (\Throwable $th) {
            DB::rollBack();
            
            return $this->sendError("Server Error.", $th->getMessage());
        }
    }

    public function update(Request $request, User $user)
    {
        $input = $request->all();

        // Validate only the canonical fields we support in the admin UI
        $validator = Validator::make($input, [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'department' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'student_no' => 'nullable|string',
            'course' => 'nullable|string',
            'yr_section' => 'nullable|string',
            'position' => 'nullable|string',
            'faculty_id' => 'nullable|string',
            'employee_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validator Error.', $validator->errors()); 
        }

        $user->name = $input['firstname'] . " " . $input['lastname'];
        $user->email = $input['email'];
        $user->updated_at = Carbon::now();

        unset($input['email']);
        $input['user_id'] = $user->id;
        $user->userDetail()->save(UserDetails::updateOrCreate($input));
        $user->save();

        return $this->sendResponse($user, "User successfully updated.");
    }

    /**
     * Return users with their user details and registered vehicles.
     * Used by frontend to preload users + vehicles for autoloading assignments.
     */
    public function usersWithVehicles()
    {
        // Exclude Admin, Guard, and Student roles from the autoload endpoint
        $excluded = ['Admin', 'Guard', 'Student'];
        $users = User::with(['userDetail', 'vehicles', 'role'])
            ->get()
            ->filter(function($u) use ($excluded) {
                $r = $u->role ? $u->role->name : null;
                return !in_array($r, $excluded);
            })
            ->map(function($u) {
            $ud = $u->userDetail;
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role ? $u->role->name : null,
                'department' => $ud->department ?? null,
                'contact_number' => $ud->contact_number ?? $u->contact ?? null,
                'or_path' => $ud->or_path ?? null,
                'cr_path' => $ud->cr_path ?? null,
                // faculty_position stored in user_details.position
                'faculty_position' => $ud->position ?? null,
                'vehicles' => $u->vehicles->map(function($v) {
                    return [
                        'id' => $v->id,
                        'user_id' => $v->user_id,
                        'plate_number' => $v->plate_number,
                        'vehicle_type' => $v->vehicle_type,
                        'vehicle_color' => $v->vehicle_color,
                        'or_path' => $v->or_path,
                        'cr_path' => $v->cr_path
                    ];
                })->toArray()
            ];
        });

        return $this->sendResponse($users, 'Users with vehicles retrieved');
    }
}
