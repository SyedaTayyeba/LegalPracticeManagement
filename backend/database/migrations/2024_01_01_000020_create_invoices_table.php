<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('cases')->nullOnDelete();

            $table->string('invoice_number', 20);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue'])->default('draft');

            $table->date('issue_date');
            $table->date('due_date');
            $table->date('paid_on')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // percentage, e.g. 8.25
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['firm_id', 'invoice_number']);
            $table->index(['firm_id', 'status']);
            $table->index(['firm_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
