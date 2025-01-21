<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jesuit_formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('stage_id')->constrained('formation_stages');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // A user can't be in multiple active formation stages
            $table->unique(['user_id', 'is_active'], 'unique_active_formation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jesuit_formations');
    }
}; 