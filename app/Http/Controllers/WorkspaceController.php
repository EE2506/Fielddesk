<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Policy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public const TIMEZONE = 'Asia/Manila';

    public function today(Request $request): Response
    {
        $now = now(self::TIMEZONE);
        $nowUtc = $now->copy()->utc();
        $tomorrowUtc = $now->copy()->addDay()->startOfDay()->utc();
        $clientIds = $request->user()->clients()->select('id');
        $followUps = FollowUp::whereIn('client_id', $clientIds)->whereNull('completed_at')
            ->with(['client:id,name,nickname,reference,mobile', 'policy:id,product'])
            ->orderBy('due_at')->paginate(30, pageName: 'follow_ups');

        $followUps->through(fn (FollowUp $item) => [
            'id' => $item->id,
            'client' => $item->client,
            'policy' => $item->policy,
            'due_at' => $item->due_at->setTimezone(self::TIMEZONE)->format('D, j M · g:i A'),
            'reason' => $item->reason,
            'group' => $item->due_at->lt($nowUtc) ? 'Overdue' : ($item->due_at->lt($tomorrowUtc) ? 'Today' : 'Upcoming callbacks'),
        ]);

        $deadlines = Policy::whereIn('client_id', $clientIds)
            ->with('client:id,name,nickname,reference')
            ->orderBy('due_on')->orderBy('id')->paginate(30, pageName: 'deadlines');

        return Inertia::render('Agent/Today', [
            'noticeThrough' => $now->copy()->addDays($request->user()->policy_notice_days)->toDateString(),
            'today' => $now->toDateString(),
            'date' => $now->format('l, j F Y'),
            'followUps' => $followUps->withQueryString(),
            'deadlines' => $deadlines->withQueryString(),
        ]);
    }

    public function clients(Request $request): Response
    {
        $query = (string) $request->session()->get('client_search', '');
        $mobile = self::normalizeMobile($query) ?? preg_replace('/\D/', '', $query);
        $search = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query).'%';
        $clients = $request->user()->clients()->when($query !== '', fn ($builder) => $builder->where(function ($builder) use ($search, $mobile) {
            $builder->whereRaw("LOWER(name) LIKE LOWER(?) ESCAPE '!'", [$search])
                ->orWhereRaw("LOWER(nickname) LIKE LOWER(?) ESCAPE '!'", [$search])
                ->orWhereRaw("LOWER(reference) LIKE LOWER(?) ESCAPE '!'", [$search]);

            if ($mobile !== '') {
                $builder->orWhere('mobile', 'like', '%'.$mobile.'%');
            }
        }))->orderByDesc('id')->paginate(25)->withQueryString();

        return Inertia::render('Agent/Clients', ['clients' => $clients, 'query' => $query]);
    }

    public function searchClients(Request $request): RedirectResponse
    {
        $data = $request->validate(['q' => 'nullable|string|max:120']);
        $request->session()->put('client_search', trim((string) ($data['q'] ?? '')));

        return to_route('workspace.clients');
    }

    public function storeClient(Request $request): RedirectResponse
    {
        $client = $this->saveClient($request);

        return to_route('workspace.client', $client);
    }

    public function client(Request $request, int $client): Response
    {
        $record = $request->user()->clients()->findOrFail($client);

        $followUps = $record->followUps()->whereNull('completed_at')->with('policy:id,product')->orderBy('due_at')->get();
        $selected = $request->integer('callback');

        return Inertia::render('Agent/Client', [
            'client' => $record,
            'policies' => $record->policies()->with('documents')->orderBy('due_on')->get(),
            'contacts' => $record->contacts()->with('policy:id,product')->latest()->orderByDesc('id')->paginate(15)->withQueryString(),
            'followUps' => $followUps,
            'selectedCallback' => $followUps->contains('id', $selected) ? $selected : null,
        ]);
    }

    public function updateClient(Request $request, int $client): RedirectResponse
    {
        $this->saveClient($request, $client);

        return to_route('workspace.client', $client);
    }

    private function saveClient(Request $request, ?int $id = null): Client
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:120',
            'nickname' => 'nullable|string|max:80',
            'mobile' => 'required|string|max:30',
            'shared_mobile' => 'sometimes|boolean',
        ]);
        $data['mobile'] = self::normalizeMobile($data['mobile']);

        if ($data['mobile'] === null) {
            throw ValidationException::withMessages(['mobile' => 'Enter a Philippine mobile number starting with 09 or +63.']);
        }

        return DB::transaction(function () use ($request, $data, $id) {
            $agent = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            $clients = $agent->clients();
            $client = $id === null ? $clients->make(['reference' => 'CL-'.Str::ulid()]) : $clients->findOrFail($id);
            $match = $agent->clients()->where('mobile', $data['mobile'])
                ->when($id !== null, fn ($query) => $query->whereKeyNot($id))->first();

            if ($match && $client->mobile !== $data['mobile'] && ! ($data['shared_mobile'] ?? false)) {
                throw ValidationException::withMessages([
                    'shared_mobile' => 'This number is already on file for '.($match->nickname ?: ($match->name ?: $match->reference)).' ('.$match->reference.'). Confirm only if they are different people.',
                ]);
            }

            $client->fill([
                'name' => $data['name'] ?? null,
                'nickname' => $data['nickname'] ?? null,
                'mobile' => $data['mobile'],
            ])->save();

            return $client;
        }, attempts: 3);
    }

    private static function normalizeMobile(string $mobile): ?string
    {
        $mobile = preg_replace('/[\s()-]/', '', trim($mobile));

        if (! preg_match('/^(?:0|\+?63)(9\d{9})$/D', $mobile, $matches)) {
            return null;
        }

        return '+63'.$matches[1];
    }

    public function storePolicy(Request $request, int $client): RedirectResponse
    {
        $request->user()->clients()->findOrFail($client)->policies()->create($this->policyFields($request));

        return to_route('workspace.client', $client);
    }

    public function updatePolicy(Request $request, int $client, int $policy): RedirectResponse
    {
        $this->policy($request, $client, $policy)->update($this->policyFields($request));

        return to_route('workspace.client', $client);
    }

    private function policyFields(Request $request): array
    {
        return $request->validate([
            'insurer' => 'required|string|max:120',
            'product' => 'required|string|max:120',
            'reference' => 'nullable|string|max:120',
            'filing_reference' => 'nullable|string|max:160',
            'event_label' => 'required|string|max:80',
            'due_on' => 'required|date_format:Y-m-d',
        ]);
    }

    public function storeDocument(Request $request, int $client, int $policy): RedirectResponse
    {
        $this->policy($request, $client, $policy)->documents()->create($request->validate([
            'name' => 'required|string|max:120',
        ]));

        return to_route('workspace.client', $client);
    }

    public function updateDocument(Request $request, int $client, int $policy, int $document): RedirectResponse
    {
        $record = $this->policy($request, $client, $policy)->documents()->findOrFail($document);
        $record->update($request->validate(['status' => ['required', Rule::in(['Missing', 'Received', 'Submitted'])]]));

        return to_route('workspace.client', $client);
    }

    private function policy(Request $request, int $client, int $policy): Policy
    {
        return $request->user()->clients()->findOrFail($client)->policies()->findOrFail($policy);
    }
}
