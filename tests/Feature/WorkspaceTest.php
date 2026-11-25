<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DevelopmentAgentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:'.base64_encode(str_repeat('f', 32))]);
        $this->withoutVite();
    }

    public function test_workspace_requires_sign_in_and_rejects_another_agents_client(): void
    {
        $owner = $this->agent();
        $agent = $this->agent();
        $client = $owner->clients()->create(['reference' => 'CL-PRIVATE', 'mobile' => '+639000000001']);

        $this->get('/workspace/clients/'.$client->id)->assertRedirect('/login');
        $this->post('/login', ['email' => $agent->email, 'password' => 'password'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($agent);

        $this->get('/workspace/clients/'.$client->id)->assertNotFound();
        $this->patch('/workspace/clients/'.$client->id, ['mobile' => '09000000002'])->assertNotFound();
        $this->get('/workspace')->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Today', false)->has('followUps.data', 0)->has('deadlines.data', 0)
        )->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_agents_cannot_attach_another_agents_policy_or_update_its_documents(): void
    {
        $owner = $this->agent();
        $agent = $this->agent();
        $foreignClient = $owner->clients()->create(['reference' => 'CL-FOREIGN', 'mobile' => '+639000000001']);
        $ownClient = $agent->clients()->create(['reference' => 'CL-OWN', 'mobile' => '+639000000002']);
        $foreignPolicy = $foreignClient->policies()->create([
            'insurer' => 'Demo', 'product' => 'Motor', 'event_label' => 'Renewal', 'due_on' => '2026-12-10',
        ]);
        $document = $foreignPolicy->documents()->create(['name' => 'Registration']);

        $this->actingAs($agent)
            ->patch("/workspace/clients/{$ownClient->id}/policies/{$foreignPolicy->id}/documents/{$document->id}", ['status' => 'Received'])
            ->assertNotFound();

        $this->post("/workspace/clients/{$ownClient->id}/contacts", [
            'submission_id' => (string) Str::uuid(),
            'policy_id' => $foreignPolicy->id,
            'outcome' => 'Reached',
        ])->assertNotFound();

        $this->assertSame('Missing', $document->fresh()->status);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_mobile_is_normalized_and_shared_numbers_require_confirmation(): void
    {
        $agent = $this->agent();
        $this->actingAs($agent);
        $this->post('/workspace/clients', ['nickname' => 'Mila', 'mobile' => '0900 000 0001'])->assertRedirect();
        $record = $agent->clients()->firstOrFail();
        $this->assertSame('+639000000001', $record->mobile);
        $this->post('/workspace/clients', ['mobile' => '+63 900-000-0001'])->assertSessionHasErrors('shared_mobile');
        $this->assertSame(1, $agent->clients()->count());
        $this->post('/workspace/clients', ['mobile' => '+63 900-000-0001', 'shared_mobile' => true])->assertSessionHasNoErrors();
        $this->post('/workspace/clients/search', ['q' => '09000000001'])->assertRedirect('/workspace/clients');
        $this->get('/workspace/clients')->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Clients', false)->has('clients.data', 2)
        );
    }

    public function test_logging_an_existing_callback_creates_one_successor_and_preserves_policy_deadlines(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 25)->setTime(6, 0));
        $agent = $this->agent();
        $client = $agent->clients()->create(['reference' => 'CL-MILA', 'mobile' => '+639000000001']);
        $motor = $client->policies()->create([
            'insurer' => 'Demo', 'product' => 'Motor', 'event_label' => 'Renewal', 'due_on' => '2026-12-10',
        ]);
        $life = $client->policies()->create([
            'insurer' => 'Demo', 'product' => 'Life', 'event_label' => 'Premium due', 'due_on' => '2026-12-02',
        ]);
        $source = $client->followUps()->create(['policy_id' => $motor->id, 'due_at' => now()->subHour(), 'reason' => 'Motor renewal quote']);
        $unrelated = $client->followUps()->create(['policy_id' => $life->id, 'due_at' => now()->addHour(), 'reason' => 'Life premium']);
        $url = "/workspace/clients/{$client->id}/contacts";
        $form = [
            'submission_id' => (string) Str::uuid(),
            'source_task_id' => $source->id,
            'outcome' => 'Callback requested',
            'note' => 'Friday at 4 PM',
            'callback_at' => '2026-11-27T16:00',
            'reason' => 'Review the motor renewal quote',
        ];

        $this->actingAs($agent);
        $this->get("/workspace/clients/{$client->id}?callback={$source->id}")->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Client', false)->where('selectedCallback', $source->id)
        );
        $this->post($url, $form)->assertRedirect()->assertSessionHasNoErrors();
        $this->post($url, $form)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('contacts', 1);
        $this->assertNotNull($source->fresh()->completed_at);
        $this->assertNull($unrelated->fresh()->completed_at);
        $this->assertDatabaseHas('follow_ups', ['policy_id' => $motor->id, 'due_at' => '2026-11-27 08:00:00', 'completed_at' => null]);
        $this->post($url, array_replace($form, ['submission_id' => (string) Str::uuid()]))->assertSessionHasErrors('source_task_id');
        $this->assertSame('2026-12-10', $motor->fresh()->due_on);
        $this->assertSame('2026-12-02', $life->fresh()->due_on);

        $this->travelTo(now()->setDate(2026, 11, 27)->setTime(4, 0));
        $this->get('/workspace')->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Today', false)
            ->has('followUps.data', 2)
            ->where('followUps.data.1.group', 'Today')
            ->where('followUps.data.1.due_at', 'Fri, 27 Nov · 4:00 PM')
            ->has('deadlines.data', 2)
        );
    }

    public function test_updating_one_requirement_does_not_change_a_policy_deadline(): void
    {
        $agent = $this->agent();
        $client = $agent->clients()->create(['reference' => 'CL-DOCS', 'mobile' => '+639000000001']);
        $policy = $client->policies()->create([
            'insurer' => 'Demo', 'product' => 'Motor', 'event_label' => 'Renewal', 'due_on' => '2026-12-10',
        ]);
        $document = $policy->documents()->create(['name' => 'Registration']);
        $url = "/workspace/clients/{$client->id}/policies/{$policy->id}/documents/{$document->id}";

        $this->actingAs($agent)->patch($url, ['status' => 'Received'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Received', $document->fresh()->status);
        $this->assertSame('2026-12-10', $policy->fresh()->due_on);
    }

    public function test_local_development_agent_seed_keeps_the_same_account_and_password(): void
    {
        $this->app['env'] = 'local';

        try {
            $this->seed(DevelopmentAgentSeeder::class);
            $agent = User::where('email', DevelopmentAgentSeeder::EMAIL)->firstOrFail();
            $this->assertSame('agent', $agent->role);
            $this->seed(DevelopmentAgentSeeder::class);
            $this->assertDatabaseCount('users', 1);
            $this->assertSame($agent->password, $agent->fresh()->password);
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    private function agent(): User
    {
        return User::create([
            'name' => 'Demo agent',
            'email' => strtolower((string) Str::ulid()).'@example.test',
            'password' => 'password',
        ]);
    }
}
