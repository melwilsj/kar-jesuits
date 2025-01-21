<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jesuit_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('dob');
            $table->date('joining_date');
            $table->date('priesthood_date')->nullable();
            $table->date('final_vows_date')->nullable();
            $table->json('academic_qualifications')->nullable();
            $table->json('publications')->nullable();
            $table->timestamps();
        });

        Schema::create('formation_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('order');
            $table->boolean('has_years')->default(false);
            $table->integer('max_years')->nullable();
            $table->timestamps();
        });

        Schema::create('jesuit_formation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('stage_id')->constrained('formation_stages');
            $table->integer('current_year')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jesuit_formation');
        Schema::dropIfExists('formation_stages');
        Schema::dropIfExists('jesuit_profiles');
    }
}; 