<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IntroductionController extends Controller
{
    public function clientPortal(Request $request): Response
    {
        $introductions = DB::table('agent_introductions')->where('requester_id', $request->user()->id)
            ->join('users', 'users.id', '=', 'agent_introductions.agent_id')
            ->select('agent_introductions.id', 'agent_introductions.status', 'users.name as agent_name')
            ->latest('agent_introductions.id')->paginate(15);

        $agents = User::where('role', 'agent')->where('accepts_introductions', true)
            ->whereNotIn('id', DB::table('agent_introductions')->where('requester_id', $request->user()->id)->select('agent_id'))
            ->orderBy('name')->orderBy('id')->paginate(15, ['id', 'name'], 'agents');

        return Inertia::render('ClientPortal', compact('introductions', 'agents'));
    }

    public function requestIntroduction(Request $request, int $agent): RedirectResponse
    {
        DB::transaction(function () use ($request, $agent) {
            $available = User::where('role', 'agent')->where('accepts_introductions', true)->lockForUpdate()->findOrFail($agent);
            $now = now();

            DB::table('agent_introductions')->insertOrIgnore([
                'requester_id' => $request->user()->id,
                'agent_id' => $available->id,
                'status' => 'Pending',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, attempts: 3);

        return to_route('client.portal');
    }

    public function inbox(Request $request): Response
    {
        $introductions = DB::table('agent_introductions')->where('agent_id', $request->user()->id)
            ->join('users', 'users.id', '=', 'agent_introductions.requester_id')
            ->selectRaw('agent_introductions.id, agent_introductions.status, users.name as client_name,
                CASE WHEN agent_introductions.status = ? THEN users.email ELSE NULL END as client_email', ['Accepted'])
            ->latest('agent_introductions.id')->paginate(15);

        return Inertia::render('Agent/Introductions', compact('introductions'));
    }

    public function respond(Request $request, int $introduction): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['Accepted', 'Declined'])]]);

        DB::transaction(function () use ($request, $introduction, $data) {
            $record = DB::table('agent_introductions')->where('agent_id', $request->user()->id)
                ->where('id', $introduction)->lockForUpdate()->first();
            abort_if($record === null, 404);

            if ($record->status === 'Pending') {
                DB::table('agent_introductions')->where('id', $record->id)->update([
                    'status' => $data['status'],
                    'updated_at' => now(),
                ]);
            }
        }, attempts: 3);

        return to_route('workspace.introductions');
    }
}
