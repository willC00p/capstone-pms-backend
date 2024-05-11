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
        return $this->sendResponse(User::all(), "Users retrieved successfully.");
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
            
                    $team->team_user()->update([
                        'user_id' => $user->id,
                        'created_at' => Carbon::now()
                    ]);
                    
                    if (!is_null($input['referred_by']) && $user->role->name == 'Member') {
                        $referrer = User::where('email', $input['referred_by'])->first();
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

        $validator = Validator::make($input, [
            'firstname' => 'required',
            'middlename' => 'nullable',
            'lastname' => 'required',
            'email' => 'required|email',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:MALE,FEMALE',
            'civil_status' => 'nullable|string|in:SINGLE,MARRIED',
            'nationality' => 'required|string',
            'religion' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'address' => 'required|string',
            'municipality' => 'required|string',
            'provice' => 'required|string',
            'country' => 'required|string',
            'zip_code' => 'required|string',
            'fb_account_name' => 'nullable|string',
            'father_firstname' => 'nullable|string',
            'father_middleinitial' => 'nullable|string',
            'father_lastname' => 'nullable|string',
            'mother_firstname' => 'nullable|string',
            'mother_middleinitial' => 'nullable|string',
            'mother_lastname' => 'nullable|string',
            'spouse_firstname' => 'nullable|string',
            'spouse_middleinitial' => 'nullable|string',
            'spouse_lastname' => 'nullable|string',
            'no_of_children' => 'nullable|string',
            'source_of_income' => 'nullable|string',
            'work_description' => 'nullable|string',
            'id_card_presented' => 'nullable|string',
            'membership_date' => 'nullable|string',
            'profile_photo_path' => 'nullable|string'
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
}
