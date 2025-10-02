<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class RegistrationQrTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_generates_svg_qr_and_stores_it()
    {
        // Use fake storage so we can assert file writes without touching disk
        Storage::fake('public');

        $payload = [
            'firstname' => 'QrTest',
            'lastname' => 'User',
            'email' => 'qrtest+' . time() . '@example.com',
            'password' => 'Secret123!',
            'c_password' => 'Secret123!',
        ];

        $response = $this->postJson('/api/register', $payload);
        $response->assertStatus(200);

        $dataUser = $response->json('data.user');
        $this->assertNotNull($dataUser, 'response does not contain data.user');

        $userId = $dataUser['id'] ?? null;
        $this->assertNotNull($userId, 'created user id missing');

        $user = User::find($userId);
        $this->assertNotNull($user, 'user not found in database');

        $ud = $user->userDetail;
        $this->assertNotNull($ud, 'user_details not created');

        $this->assertNotEmpty($ud->qr_path, 'qr_path was not set on user_details');

        // Assert that the file was written to the public disk
        Storage::disk('public')->assertExists($ud->qr_path);
    }
}
