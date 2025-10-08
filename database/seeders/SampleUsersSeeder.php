<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\User;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $base = base_path('sample-or-cr');
        $orSample = file_exists($base . DIRECTORY_SEPARATOR . 'or.pdf') ? $base . DIRECTORY_SEPARATOR . 'or.pdf' : null;
        $crSample = file_exists($base . DIRECTORY_SEPARATOR . 'cr.pdf') ? $base . DIRECTORY_SEPARATOR . 'cr.pdf' : null;

        if (!$orSample && !$crSample) {
            $this->command->info('No sample OR/CR files found in sample-or-cr; skipping file copy, will still create users without or_path/cr_path.');
        }

        // Ensure roles exist
        $studentRole = Role::firstOrCreate(['name' => 'Student']);
        $facultyRole = Role::firstOrCreate(['name' => 'Faculty']);
        $employeeRole = Role::firstOrCreate(['name' => 'Employee']);

        // Prepare destination dir
        $destDir = storage_path('app/public/or_cr');
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        // Create 5 students
        for ($i = 1; $i <= 5; $i++) {
            $email = "student{$i}@example.com";
            DB::table('users')->where('email', $email)->delete();
            $user = User::create([
                'roles_id' => $studentRole->id,
                'name' => "Student {$i} Test",
                'email' => $email,
                'password' => Hash::make('password'),
            ]);

            $or_path = null;
            $cr_path = null;
            if ($orSample) {
                $filename = 'or_student_' . $i . '_' . Str::random(6) . '.' . pathinfo($orSample, PATHINFO_EXTENSION);
                $dest = $destDir . DIRECTORY_SEPARATOR . $filename;
                @copy($orSample, $dest);
                $or_path = 'or_cr/' . $filename;
            }
            if ($crSample) {
                $filename = 'cr_student_' . $i . '_' . Str::random(6) . '.' . pathinfo($crSample, PATHINFO_EXTENSION);
                $dest = $destDir . DIRECTORY_SEPARATOR . $filename;
                @copy($crSample, $dest);
                $cr_path = 'or_cr/' . $filename;
            }

            $user->userDetail()->create([
                'user_id' => $user->id,
                'firstname' => "Student{$i}",
                'lastname' => 'Test',
                'department' => 'Sample Dept',
                'student_no' => sprintf('S%03d', $i),
                'course' => 'Sample Course',
                'yr_section' => '1-A',
                'contact_number' => '0912345678',
                'plate_number' => 'ABC-'.str_pad($i,3,'0',STR_PAD_LEFT),
                'or_path' => $or_path,
                'cr_path' => $cr_path,
                'from_pending' => false,
            ]);
        }

        // Create 5 faculty
        for ($i = 1; $i <= 5; $i++) {
            $email = "faculty{$i}@example.com";
            DB::table('users')->where('email', $email)->delete();
            $user = User::create([
                'roles_id' => $facultyRole->id,
                'name' => "Faculty {$i} Test",
                'email' => $email,
                'password' => Hash::make('password'),
            ]);

            $or_path = null;
            $cr_path = null;
            if ($orSample) {
                $filename = 'or_faculty_' . $i . '_' . Str::random(6) . '.' . pathinfo($orSample, PATHINFO_EXTENSION);
                $dest = $destDir . DIRECTORY_SEPARATOR . $filename;
                @copy($orSample, $dest);
                $or_path = 'or_cr/' . $filename;
            }
            if ($crSample) {
                $filename = 'cr_faculty_' . $i . '_' . Str::random(6) . '.' . pathinfo($crSample, PATHINFO_EXTENSION);
                $dest = $destDir . DIRECTORY_SEPARATOR . $filename;
                @copy($crSample, $dest);
                $cr_path = 'or_cr/' . $filename;
            }

            $user->userDetail()->create([
                'user_id' => $user->id,
                'firstname' => "Faculty{$i}",
                'lastname' => 'Test',
                'department' => 'Sample Dept',
                'position' => 'Professor',
                'faculty_id' => sprintf('F%03d', $i),
                'contact_number' => '0912345678',
                'plate_number' => 'FAC-'.str_pad($i,3,'0',STR_PAD_LEFT),
                'or_path' => $or_path,
                'cr_path' => $cr_path,
                'from_pending' => false,
            ]);
        }

        // Create 5 employees
        for ($i = 1; $i <= 5; $i++) {
            $email = "employee{$i}@example.com";
            DB::table('users')->where('email', $email)->delete();
            $user = User::create([
                'roles_id' => $employeeRole->id,
                'name' => "Employee {$i} Test",
                'email' => $email,
                'password' => Hash::make('password'),
            ]);

            $or_path = null;
            $cr_path = null;
            if ($orSample) {
                $filename = 'or_employee_' . $i . '_' . Str::random(6) . '.' . pathinfo($orSample, PATHINFO_EXTENSION);
                $dest = $destDir . DIRECTORY_SEPARATOR . $filename;
                @copy($orSample, $dest);
                $or_path = 'or_cr/' . $filename;
            }
            if ($crSample) {
                $filename = 'cr_employee_' . $i . '_' . Str::random(6) . '.' . pathinfo($crSample, PATHINFO_EXTENSION);
                $dest = $destDir . DIRECTORY_SEPARATOR . $filename;
                @copy($crSample, $dest);
                $cr_path = 'or_cr/' . $filename;
            }

            $user->userDetail()->create([
                'user_id' => $user->id,
                'firstname' => "Employee{$i}",
                'lastname' => 'Test',
                'department' => 'Sample Dept',
                'position' => 'Staff',
                'employee_id' => sprintf('E%03d', $i),
                'contact_number' => '0912345678',
                'plate_number' => 'EMP-'.str_pad($i,3,'0',STR_PAD_LEFT),
                'or_path' => $or_path,
                'cr_path' => $cr_path,
                'from_pending' => false,
            ]);
        }

        $this->command->info('Sample users seeded: 5 Students, 5 Faculty, 5 Employees.');
    }
}
