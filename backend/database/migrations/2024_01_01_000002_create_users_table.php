<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Nullable because Platform Admins are not attached to any firm.
            $table->foreignId('firm_id')->nullable()->constrained('firms')->cascadeOnDelete();

            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Enum-backed role — kept simple and fast for tenant-scoped role checks.
            // See App\Enums\UserRole. Fine-grained permissions layer on top via spatie/laravel-permission.
            $table->enum('role', ['platform_admin', 'firm_owner', 'lawyer', 'paralegal', 'client'])
                ->default('client');

            $table->string('phone')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('title')->nullable(); // e.g. "Senior Partner"
            $table->enum('status', ['active', 'invited', 'suspended'])->default('active');

            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // A user's email must be unique *within* a firm, not globally —
            // the same person could legitimately be a client of two different firms.
            $table->unique(['firm_id', 'email']);
            $table->index(['firm_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
