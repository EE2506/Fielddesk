<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function store(Request $request, int $client): RedirectResponse
    {
        $data = $request->validate([
            'submission_id' => 'required|uuid',
            'policy_id' => 'nullable|integer',
            'source_task_id' => 'nullable|integer',
            'outcome' => ['required', Rule::in(['Reached', 'No answer', 'Callback requested'])],
            'note' => 'nullable|string|max:500',
            'callback_at' => 'required_if:outcome,Callback requested|nullable|date_format:Y-m-d\TH:i',
            'reason' => 'required_with:callback_at|nullable|string|max:120',
        ]);

        DB::transaction(function () use ($request, $client, $data): void {
            $record = $request->user()->clients()->lockForUpdate()->findOrFail($client);

            if ($record->contacts()->where('submission_id', $data['submission_id'])->exists()) {
                return;
            }

            $source = null;
            if (isset($data['source_task_id'])) {
                $source = $record->followUps()->findOrFail($data['source_task_id']);

                if ($source->completed_at !== null) {
                    throw ValidationException::withMessages(['source_task_id' => 'This callback was already handled. Reopen the client to review the latest follow-ups.']);
                }
            }

            $policyId = $source ? $source->policy_id : ($data['policy_id'] ?? null);
            if ($policyId !== null) {
                $record->policies()->findOrFail($policyId);
            }

            $dueAt = isset($data['callback_at'])
                ? Carbon::createFromFormat('!Y-m-d\TH:i', $data['callback_at'], WorkspaceController::TIMEZONE)->utc()
                : null;

            if ($dueAt !== null && $dueAt->lte(now())) {
                throw ValidationException::withMessages(['callback_at' => 'Choose a future time in Manila.']);
            }

            $contact = $record->contacts()->create([
                'policy_id' => $policyId,
                'submission_id' => $data['submission_id'],
                'outcome' => $data['outcome'],
                'note' => $data['note'] ?? null,
            ]);
            $source?->update(['completed_at' => now()]);

            if ($dueAt !== null) {
                $record->followUps()->create([
                    'policy_id' => $policyId,
                    'contact_id' => $contact->id,
                    'due_at' => $dueAt,
                    'reason' => $data['reason'],
                ]);
            }
        }, attempts: 3);

        return to_route('workspace.client', $client);
    }
}
