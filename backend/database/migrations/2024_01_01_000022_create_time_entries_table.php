<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('description');
            $table->unsignedInteger('minutes');
            $table->decimal('hourly_rate', 10, 2);
            $table->boolean('billable')->default(true);
            $table->date('entry_date');

            $table->foreignId('invoice_line_item_id')->nullable()->constrained('invoice_line_items')->nullOnDelete();

            $table->timestamps();

            $table->index(['firm_id', 'case_id']);
            $table->index(['firm_id', 'user_id']);
            $table->index(['firm_id', 'billable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
