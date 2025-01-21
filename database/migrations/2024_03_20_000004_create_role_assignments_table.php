<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['community', 'institution', 'province']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('role_type_id')->constrained('role_types');
            $table->morphs('assignable'); // For Community/Institution/Province
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure a user doesn't have multiple active roles of same type
            $table->unique(['user_id', 'role_type_id', 'assignable_id', 'assignable_type'], 'unique_active_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_assignments');
        Schema::dropIfExists('role_types');
    }
}; 