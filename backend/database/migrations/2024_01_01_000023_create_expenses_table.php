<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('cases')->nullOnDelete();

            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('incurred_on');
            $table->boolean('billable')->default(true);
            $table->foreignId('invoice_line_item_id')->nullable()->constrained('invoice_line_items')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['firm_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
