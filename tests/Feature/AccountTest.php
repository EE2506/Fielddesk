<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('f', 32))]);
        $this->withoutVite();
    }

    public function test_registration_normalizes_email_and_routes_each_role_to_its_own_area(): void
    {
        $password = 'long-local-test-password';
        $this->get('/register')->assertInertia(fn (Assert $page) => $page->component('Register', false));

        $this->post('/register', [
            'name' => 'Test Client', 'email' => 'Client@Example.test',
            'role' => 'client', 'password' => $password, 'password_confirmation' => $password,
        ])->assertRedirect(route('client.portal'));

        $user = User::where('email', 'client@example.test')->firstOrFail();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->get('/workspace')->assertForbidden();
        $this->post('/workspace/clients', ['mobile' => '09000000001'])->assertForbidden();
        $this->get('/portal')->assertOk();
        $this->post('/workspace/logout')->assertRedirect(route('login'));

        $this->post('/register', [
            'name' => 'Test Agent', 'email' => 'agent@example.test',
            'role' => 'agent', 'password' => $password, 'password_confirmation' => $password,
        ])->assertRedirect(route('workspace.today'));

        $this->get('/portal')->assertForbidden();
        $this->get('/workspace')->assertOk();
    }

    public function test_introduction_requests_are_private_and_share_contact_only_after_acceptance(): void
    {
        $client = User::factory()->create();
        $client->forceFill(['role' => 'client'])->save();
        $agent = User::factory()->create();
        $otherAgent = User::factory()->create();

        $this->actingAs($agent)->patch('/workspace/settings', [
            'policy_notice_days' => 12, 'accepts_introductions' => true,
        ])->assertRedirect();
        $this->assertSame(12, $agent->fresh()->policy_notice_days);

        $this->actingAs($client)->get('/portal')->assertInertia(fn (Assert $page) => $page
            ->component('ClientPortal', false)
            ->where('agents.data.0.id', $agent->id)
            ->missing('agents.data.0.email')
            ->has('agents.data', 1)
        );
        $this->post('/portal/agents/'.$otherAgent->id)->assertNotFound();
        $this->post('/portal/agents/'.$agent->id)->assertRedirect();
        $this->post('/portal/agents/'.$agent->id)->assertRedirect();
        $this->assertDatabaseCount('agent_introductions', 1);
        $id = \Illuminate\Support\Facades\DB::table('agent_introductions')->value('id');

        $this->actingAs($otherAgent)->patch('/workspace/introductions/'.$id, ['status' => 'Accepted'])->assertNotFound();

        $this->actingAs($agent)->get('/workspace/introductions')->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Introductions', false)->where('introductions.data.0.client_email', null)
        );
        $this->patch('/workspace/introductions/'.$id, ['status' => 'Accepted'])->assertRedirect();
        $this->get('/workspace/introductions')->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Introductions', false)->where('introductions.data.0.client_email', $client->email)
        );
        $this->actingAs($client)->get('/portal')->assertInertia(fn (Assert $page) => $page
            ->component('ClientPortal', false)->where('introductions.data.0.status', 'Accepted')
        );
    }
}
