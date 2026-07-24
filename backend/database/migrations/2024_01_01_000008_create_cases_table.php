<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();

            // Human-readable, firm-scoped case number e.g. "2026-0143" — see
            // CaseService::nextCaseNumber(). Unique per firm, not globally.
            $table->string('case_number', 20);

            $table->string('title');
            $table->string('case_type'); // e.g. Litigation, Family Law, Estate Planning, Corporate
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('lead_lawyer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('opposing_party')->nullable();
            $table->string('opposing_counsel')->nullable();

            $table->string('court_name')->nullable();
            $table->string('court_case_number')->nullable();
            $table->string('court_jurisdiction')->nullable();

            $table->enum('status', ['new', 'investigation', 'active', 'waiting', 'completed', 'closed'])
                ->default('new');

            $table->date('opened_on')->nullable();
            $table->date('filed_on')->nullable();
            $table->date('closed_on')->nullable();

            $table->text('description')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['firm_id', 'case_number']);
            $table->index(['firm_id', 'status']);
            $table->index(['firm_id', 'client_id']);
            $table->index(['firm_id', 'lead_lawyer_id']);
            $table->fullText(['title', 'case_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
