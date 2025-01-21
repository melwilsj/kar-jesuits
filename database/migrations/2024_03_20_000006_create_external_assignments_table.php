<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistancies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('common_houses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('assistancy_id')->constrained();
            $table->text('address');
            $table->json('contact_details')->nullable();
            $table->timestamps();
        });

        Schema::create('external_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->morphs('assignable'); // For common_houses or external_communities
            $table->string('assignment_type'); // studies, work, sabbatical
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('province_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('from_province_id')->constrained('provinces');
            $table->foreignId('to_province_id')->constrained('provinces');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed']);
            $table->date('request_date');
            $table->date('completion_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('province_transfers');
        Schema::dropIfExists('external_assignments');
        Schema::dropIfExists('common_houses');
        Schema::dropIfExists('assistancies');
    }
}; 