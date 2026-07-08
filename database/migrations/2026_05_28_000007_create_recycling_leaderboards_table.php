<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recycling_leaderboard', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained('users')->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->string('month');
            $table->integer('year');
            $table->integer('rank')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recycling_leaderboard');
    }
};
