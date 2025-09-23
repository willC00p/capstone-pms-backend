<?php
   
namespace App\Http\Controllers\API;
   
use App\Models\TeamUser;
use App\Models\UserDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Support\Str;
use App\Models\Vehicle;
   
class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'c_password' => 'required|same:password',
            // vehicle / OR-CR uploads
            'plate_number' => 'nullable|string',
            'vehicle_color' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'or_file' => 'sometimes|file|mimes:pdf|max:5120',
            'cr_file' => 'sometimes|file|mimes:pdf|max:5120',
            'or_cr_pdf' => 'sometimes|file|mimes:pdf|max:5120',
            'or_number' => 'nullable|string|unique:vehicles,or_number',
            'cr_number' => 'nullable|string|unique:vehicles,cr_number',
            'referred_by' => [
                'nullable', 
                'email', 
                Rule::exists('users', 'email'), 
                function (string $attribute, mixed $value, \Closure $fail) {
                    $referrer = User::where('email', $value)->first();
                    if ($referrer && $referrer->refer_count >= 10) { $fail("Referrer exceeded count of refers."); }
                }
            ]
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $new_user = [
            'name' => $input['firstname'] . " " . $input['lastname'],
            'email' => $input['email'],
            'password' => $input['password']
        ];
        $user = User::create($new_user);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
        $user->userDetail()->save(UserDetails::create([
            'user_id' => $user->id,
            'firstname' => $input['firstname'],
            'lastname' => $input['lastname'],
            'nationality' => 'Filipino',
            'membership_date' => $user->created_at,
            // placeholders for OR/CR paths (may be set below)
            'or_path' => null,
            'cr_path' => null,
            'or_number' => $input['or_number'] ?? null,
            'cr_number' => $input['cr_number'] ?? null,
            'plate_number' => $input['plate_number'] ?? null,
        ]));

        // Handle vehicle OR/CR upload and vehicle creation if plate_number supplied
        $orPath = null; $crPath = null;
        if (isset($input['or_file']) || isset($input['cr_file']) || isset($input['or_cr_pdf'])) {
            // Note: incoming files are in the request, so access via $request
            if ($request->hasFile('or_cr_pdf')) {
                $file = $request->file('or_cr_pdf');
                $filename = 'orcr_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $orPath = $file->storeAs('or_cr', $filename, 'public');
            }
            if ($request->hasFile('or_file')) {
                $file = $request->file('or_file');
                $filename = 'or_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $orPath = $file->storeAs('or_cr', $filename, 'public');
            }
            if ($request->hasFile('cr_file')) {
                $file = $request->file('cr_file');
                $filename = 'cr_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $crPath = $file->storeAs('or_cr', $filename, 'public');
            }

            // update the userDetails with or/cr paths
            $ud = $user->userDetail()->first();
            if ($ud) {
                $ud->or_path = $orPath ?? $ud->or_path;
                $ud->cr_path = $crPath ?? $ud->cr_path;
                $ud->or_number = $input['or_number'] ?? $ud->or_number;
                $ud->cr_number = $input['cr_number'] ?? $ud->cr_number;
                $ud->save();
            }
        }

        // Create a Vehicle record if a plate number was provided
        if (!empty($input['plate_number'])) {
            $vehicleData = [
                'user_id' => $user->id,
                'user_details_id' => $user->userDetail()->first()->id ?? null,
                'plate_number' => $input['plate_number'],
                'vehicle_color' => $input['vehicle_color'] ?? null,
                'vehicle_type' => $input['vehicle_type'] ?? null,
                'brand' => $input['brand'] ?? null,
                'model' => $input['model'] ?? null,
                'or_path' => $orPath,
                'cr_path' => $crPath,
                'or_number' => $input['or_number'] ?? null,
                'cr_number' => $input['cr_number'] ?? null,
            ];
            Vehicle::create($vehicleData);
        }

        $team = $user->myTeam()->create([
            'user_id' => $user->id,
            'name' => $user->name . "'s TEAM",
            'personal_team' => 1,
        ]);

        $team->team_user()->update([
            'user_id' => $user->id,
            'created_at' => Carbon::now()
        ]);
        
        if (!is_null($input['referred_by'])) {
            $referrer = User::where('email', $input['referred_by'])->first();
            $referrer->refer_count = $referrer->refer_count + 1;
            $referrer->save();

            $team->team_user()->update([
                'user_id' => $user->id,
                'lead_id' => $referrer->id,
            ]);
        }
   
        return $this->sendResponse($success, 'User register successfully.');
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
        'remember' => 'boolean'
    ]);

    $credentials = $request->only('email', 'password');
    $remember = $request->remember ?? false;

    if (!Auth::attempt($credentials, $remember)) {
        return response()->json(['message' => 'Email or Password is incorrect.'], 401);
    }

    $user = Auth::user();
    $success['token'] = $user->createToken('MyApp')->plainTextToken;
    $success['name'] = $user->name;

    return response()->json(['data' => $success, 'message' => 'Login Successfully'], 200);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'Logged out successfully');
    }
}