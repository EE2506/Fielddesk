<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_signup_uses_existing_registration_and_opens_the_protected_portal(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('f', 32))]);
        $this->withoutVite();

        $this->get('/portal')->assertRedirect(route('login'));
        $this->get('/client/signup')->assertInertia(fn (Assert $page) => $page
            ->component('ClientSignup')
        );

        $this->from('/client/signup')->post('/register', [
            'name' => 'Test Client',
            'email' => 'client@example.test',
            'role' => 'client',
            'password' => 'local-client-test-password',
            'password_confirmation' => 'different-password',
        ])->assertRedirect('/client/signup')->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);

        $this->post('/register', [
            'name' => 'Test Client',
            'email' => 'client@example.test',
            'role' => 'client',
            'password' => 'local-client-test-password',
            'password_confirmation' => 'local-client-test-password',
        ])->assertRedirect(route('client.portal'));

        $client = User::where('email', 'client@example.test')->firstOrFail();
        $this->assertSame('client', $client->role);
        $this->assertTrue(Hash::check('local-client-test-password', $client->password));
        $this->get('/portal')->assertOk();
        $this->get('/workspace')->assertForbidden();
        $this->get('/client/signup')->assertRedirect(route('client.portal'));
    }

    public function test_dashboard_shows_only_the_clients_profile_and_introductions(): void
    {
        $this->withoutVite();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('f', 32))]);

        $client = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);
        $agent = User::factory()->create();
        $otherAgent = User::factory()->create();
        DB::table('agent_introductions')->insert([
            ['requester_id' => $client->id, 'agent_id' => $agent->id, 'status' => 'Accepted'],
            ['requester_id' => $otherClient->id, 'agent_id' => $otherAgent->id, 'status' => 'Accepted'],
        ]);

        $this->actingAs($client)->get('/portal?page=2')->assertInertia(fn (Assert $page) => $page
            ->component('ClientPortal')
            ->where('profile.name', $client->name)
            ->where('profile.email', $client->email)
            ->missing('profile.id')
            ->where('connectedAgent.agent_name', $agent->name)
            ->missing('connectedAgent.email')
        );
        $this->get('/portal')->assertInertia(fn (Assert $page) => $page
            ->has('introductions.data', 1)
            ->where('introductions.data.0.agent_name', $agent->name)
        );
    }

}
