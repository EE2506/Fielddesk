<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 29)->unique();
            $table->string('name', 120)->nullable();
            $table->string('nickname', 80)->nullable();
            $table->string('mobile', 13);
            $table->timestamps();
            $table->index(['user_id', 'mobile']);
        });

        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('insurer', 120);
            $table->string('product', 120);
            $table->string('reference', 120)->nullable();
            $table->string('filing_reference', 160)->nullable();
            $table->string('event_label', 80);
            $table->date('due_on')->index();
            $table->timestamps();
        });

        Schema::create('policy_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('status', 12)->default('Missing');
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('submission_id');
            $table->string('outcome', 24);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'submission_id']);
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('due_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('reason', 120);
            $table->timestamps();
            $table->index(['client_id', 'completed_at', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('policy_documents');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('clients');
    }
};
