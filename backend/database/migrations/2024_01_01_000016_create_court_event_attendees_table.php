<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_event_id')->constrained('court_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['court_event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_event_attendees');
    }
};
