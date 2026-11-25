<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\IntroductionController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceSettingsController;
use App\Http\Middleware\AccountRole;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response(File::get(base_path('marketing/fielddesk-landing.html'))));
Route::get('/fielddesk-landing.css', fn () => response(File::get(base_path('marketing/fielddesk-landing.css')), 200, [
    'Content-Type' => 'text/css; charset=UTF-8',
]));

foreach (['agent', 'client', 'policy', 'document', 'introduction'] as $parameter) {
    Route::pattern($parameter, '[0-9]+');
}

Route::middleware('guest')->group(function () {
    Route::inertia('/client/signup', 'ClientSignup')->name('client.signup');
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/register', [SessionController::class, 'registration'])->name('register');
    Route::post('/register', [SessionController::class, 'register'])->middleware('throttle:5,1');
});

Route::middleware('auth')->post('/workspace/logout', [SessionController::class, 'destroy']);

Route::middleware(['auth', AccountRole::class.':client', 'cache.headers:private;no_store'])->prefix('portal')->group(function () {
    Route::get('/', [IntroductionController::class, 'clientPortal'])->name('client.portal');
    Route::post('/agents/{agent}', [IntroductionController::class, 'requestIntroduction'])->middleware('throttle:10,1');
});

Route::middleware(['auth', AccountRole::class.':agent', 'cache.headers:private;no_store'])->prefix('workspace')->group(function () {
    Route::get('/', [WorkspaceController::class, 'today'])->name('workspace.today');
    Route::get('/clients', [WorkspaceController::class, 'clients'])->name('workspace.clients');
    Route::post('/clients/search', [WorkspaceController::class, 'searchClients']);
    Route::post('/clients', [WorkspaceController::class, 'storeClient']);
    Route::get('/clients/{client}', [WorkspaceController::class, 'client'])->name('workspace.client');
    Route::patch('/clients/{client}', [WorkspaceController::class, 'updateClient']);
    Route::post('/clients/{client}/policies', [WorkspaceController::class, 'storePolicy']);
    Route::patch('/clients/{client}/policies/{policy}', [WorkspaceController::class, 'updatePolicy']);
    Route::post('/clients/{client}/policies/{policy}/documents', [WorkspaceController::class, 'storeDocument']);
    Route::patch('/clients/{client}/policies/{policy}/documents/{document}', [WorkspaceController::class, 'updateDocument']);
    Route::post('/clients/{client}/contacts', [ContactController::class, 'store']);
    Route::get('/introductions', [IntroductionController::class, 'inbox'])->name('workspace.introductions');
    Route::patch('/introductions/{introduction}', [IntroductionController::class, 'respond']);
    Route::get('/settings', [WorkspaceSettingsController::class, 'show'])->name('workspace.settings');
    Route::patch('/settings', [WorkspaceSettingsController::class, 'update']);
});
