<?php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Thường Mai',
        'email'                 => 'thuongmai@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['token','user' => ['id','name','email']]);

    $this->assertDatabaseHas('users', ['email' => 'thuongmai@example.com']);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()->assertJsonStructure(['token','user']);
});

test('user cannot login with wrong password', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(401);
});

test('user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/v1/auth/logout')
         ->assertOk();
});
