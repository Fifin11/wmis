<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recycling_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained('users')->onDelete('cascade');
            $table->text('description')->nullable(); // Citizen's description of what they recycled
            $table->integer('claimed_points')->default(0); // Points citizen is claiming
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('admin_note')->nullable(); // Admin's review note / rejection reason
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recycling_submissions');
    }
};
