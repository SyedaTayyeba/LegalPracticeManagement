<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // used in API responses, never expose incrementing id
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();

            // Subscription-ready fields (Module 12 will build on these)
            $table->string('plan')->default('solo'); // solo | professional | enterprise
            $table->unsignedInteger('seat_limit')->default(1);
            $table->unsignedInteger('storage_limit_mb')->default(1024);
            $table->timestamp('trial_ends_at')->nullable();
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');

            $table->foreignId('owner_id')->nullable(); // set after owner user is created
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firms');
    }
};
