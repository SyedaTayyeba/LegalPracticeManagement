<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('cases')->cascadeOnDelete();

            $table->string('title');
            $table->enum('event_type', ['hearing', 'deadline', 'meeting', 'other'])->default('hearing');
            $table->text('notes')->nullable();
            $table->string('location')->nullable();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            $table->foreignId('lead_lawyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('reminder_sent')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['firm_id', 'starts_at']);
            $table->index(['firm_id', 'lead_lawyer_id', 'starts_at']);
            $table->index(['firm_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_events');
    }
};
