<?php
   
namespace App\Http\Controllers\API;
   
use App\Models\UserDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
   
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
            'membership_date' => $user->created_at
        ]));
   
        return $this->sendResponse($success, 'User register successfully.');
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
            $success['name'] =  $user->name;
   
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }
}