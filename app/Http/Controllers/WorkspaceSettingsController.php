<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceSettingsController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('Agent/WorkspaceSettings', [
            'account' => $request->user()->only(['name', 'policy_notice_days', 'accepts_introductions']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'policy_notice_days' => 'required|integer|between:1,30',
            'accepts_introductions' => 'required|boolean',
        ]);
        $request->user()->forceFill($data)->save();

        return to_route('workspace.settings');
    }
}
