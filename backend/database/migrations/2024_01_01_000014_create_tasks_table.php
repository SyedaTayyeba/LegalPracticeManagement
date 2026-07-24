<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('cases')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');

            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('reminder_sent')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['firm_id', 'status']);
            $table->index(['firm_id', 'assigned_to']);
            $table->index(['firm_id', 'due_date']);
            $table->index(['firm_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
