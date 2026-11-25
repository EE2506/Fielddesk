<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 12)->default('agent');
            $table->unsignedTinyInteger('policy_notice_days')->default(7);
            $table->boolean('accepts_introductions')->default(false);
        });

        Schema::create('agent_introductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 12)->default('Pending');
            $table->timestamps();
            $table->unique(['requester_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_introductions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'policy_notice_days', 'accepts_introductions']);
        });
    }
};
