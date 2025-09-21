<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends BaseController
{
    // Create a student account (approved)
    public function createStudent(Request $request)
    {
        // Per requirement: Student must provide OR/CR PDF as well
        $v = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'department' => 'nullable|string',
            'student_no' => 'nullable|string',
            'course' => 'nullable|string',
            'yr_section' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'faculty_id' => 'nullable|string',
            'employee_id' => 'nullable|string',
            'or_file' => 'sometimes|file|mimes:pdf|max:5120',
            'cr_file' => 'sometimes|file|mimes:pdf|max:5120',
            'or_cr_pdf' => 'sometimes|file|mimes:pdf|max:5120',
        ]);

        if ($v->fails()) return $this->sendError('Validation error', $v->errors());


        // ensure role exists and assign
        $role = Role::firstOrCreate(['name' => 'Student']);

        $user = User::create([
            'roles_id' => $role->id,
            'name' => $request->firstname . ' ' . $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $orPath = null;
        $crPath = null;
        // Backwards compatible: accept a single or_cr_pdf or separate or_file/cr_file
        if ($request->hasFile('or_cr_pdf')) {
            $file = $request->file('or_cr_pdf');
            $filename = 'orcr_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $stored = $file->storeAs('or_cr', $filename, 'public');
            $orPath = $stored;
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

        // Store only the requested student details
        $user->userDetail()->create([
            'user_id' => $user->id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'department' => $request->department,
            'student_no' => $request->student_no,
            'course' => $request->course,
            'yr_section' => $request->yr_section,
            'faculty_id' => $request->faculty_id,
            'employee_id' => $request->employee_id,
            'contact_number' => $request->contact_number,
            'plate_number' => $request->plate_number,
            'or_path' => $orPath,
            'cr_path' => $crPath,
            'from_pending' => false,
            'membership_date' => $user->created_at,
        ]);

        return $this->sendResponse(['id' => $user->id], 'Student account created.');
    }

    // Create faculty account (approved). Requires OR/CR upload (pdf)
    public function createFaculty(Request $request)
    {
        $v = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'faculty_id' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'or_file' => 'sometimes|file|mimes:pdf|max:5120',
            'cr_file' => 'sometimes|file|mimes:pdf|max:5120',
            'or_cr_pdf' => 'sometimes|file|mimes:pdf|max:5120',
        ]);

        if ($v->fails()) return $this->sendError('Validation error', $v->errors());

        $role = Role::firstOrCreate(['name' => 'Faculty']);
        $user = User::create([
            'roles_id' => $role->id,
            'name' => $request->firstname . ' ' . $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $orPath = null;
        $crPath = null;
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
        // Store only the requested details for Faculty
        $user->userDetail()->create([
            'user_id' => $user->id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'department' => $request->department,
            'position' => $request->position,
            'faculty_id' => $request->faculty_id,
            'contact_number' => $request->contact_number,
            'plate_number' => $request->plate_number,
            'or_path' => $orPath,
            'cr_path' => $crPath,
            'from_pending' => false,
            'membership_date' => $user->created_at,
        ]);

        return $this->sendResponse(['id' => $user->id], 'Faculty account created.');
    }

    // Create employee account (approved). Requires OR/CR upload (pdf)
    public function createEmployee(Request $request)
    {
        $v = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'employee_id' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'or_file' => 'sometimes|file|mimes:pdf|max:5120',
            'cr_file' => 'sometimes|file|mimes:pdf|max:5120',
            'or_cr_pdf' => 'sometimes|file|mimes:pdf|max:5120',
        ]);

        if ($v->fails()) return $this->sendError('Validation error', $v->errors());

        $role = Role::firstOrCreate(['name' => 'Employee']);
        $user = User::create([
            'roles_id' => $role->id,
            'name' => $request->firstname . ' ' . $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $orPath = null;
        $crPath = null;
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
        // Store only the requested details for Employee
        $user->userDetail()->create([
            'user_id' => $user->id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'department' => $request->department,
            'position' => $request->position,
            'employee_id' => $request->employee_id,
            'contact_number' => $request->contact_number,
            'plate_number' => $request->plate_number,
            'or_path' => $orPath,
            'cr_path' => $crPath,
            'from_pending' => false,
            'membership_date' => $user->created_at,
        ]);

        return $this->sendResponse(['id' => $user->id], 'Employee account created.');
    }

    // Create guard accounts from pending list: accepts username,email,password
    public function createGuard(Request $request)
    {
        $v = Validator::make($request->all(), [
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        if ($v->fails()) return $this->sendError('Validation error', $v->errors());

        $role = Role::firstOrCreate(['name' => 'Guard']);
        $user = User::create([
            'roles_id' => $role->id,
            'name' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // For guards, only create a minimal user details record (username stored as firstname)
        $user->userDetail()->create([
            'user_id' => $user->id,
            'firstname' => $request->username,
            'lastname' => '',
            'from_pending' => true,
            'membership_date' => $user->created_at,
        ]);

        return $this->sendResponse(['id' => $user->id], 'Guard account created.');
    }
}
