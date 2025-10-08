<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminCreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_student()
    {
        // create admin user
        $admin = User::factory()->create();
        
            // ensure Admin role exists and assign to the admin user
            $role = \App\Models\Role::firstOrCreate(['name' => 'Admin']);
            $admin->roles_id = $role->id;
            $admin->save();
        \Illuminate\Support\Facades\Storage::fake('public');
        $pdf = \Illuminate\Http\UploadedFile::fake()->create('or_cr.pdf', 100, 'application/pdf');

        $this->actingAs($admin, 'sanctum')
            ->post('/api/admin/create-student', [
                'firstname' => 'Test',
                'lastname' => 'Student',
                'email' => 'student@example.com',
                'password' => 'secret123',
                'or_cr_pdf' => $pdf,
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['id']]);

        $this->assertDatabaseHas('users', ['email' => 'student@example.com']);
    }
}
