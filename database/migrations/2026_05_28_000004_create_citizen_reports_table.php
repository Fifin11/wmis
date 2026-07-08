<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citizen_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('issue_type', ['Missed Pickup', 'Illegal Dumping', 'Hazardous Waste']);
            $table->decimal('location_lat', 10, 8);
            $table->decimal('location_lng', 11, 8);
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Open', 'Investigating', 'Resolved'])->default('Open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizen_reports');
    }
};