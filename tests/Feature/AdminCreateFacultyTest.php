<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class AdminCreateFacultyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_faculty_with_or_cr_pdf()
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(['name' => 'Admin']);
        $admin->roles_id = $role->id;
        $admin->save();

        $file = UploadedFile::fake()->create('or_cr.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/create-faculty', [
                'firstname' => 'Prof',
                'lastname' => 'Smith',
                'email' => 'prof@example.com',
                'password' => 'secret123',
                'or_cr_pdf' => $file,
            ]);

        $response->assertStatus(200)->assertJsonStructure(['success', 'data' => ['id']]);

        $this->assertDatabaseHas('users', ['email' => 'prof@example.com']);
        Storage::disk('public')->assertExists('or_cr');
    }
}
