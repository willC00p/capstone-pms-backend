<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\UserDetails;
use Carbon\Carbon;
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
