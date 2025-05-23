<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jesuits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained();
            $table->foreignId('province_id')->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('current_community_id')->nullable()->constrained('communities');
            $table->string('code')->unique()->comment('Unique Jesuit identification code');
            $table->enum('category', ['Bp', 'P', 'S', 'NS', 'F'])->comment('Bishop, Priest, Scholastic, Novice, Brother');
            $table->date('dob');
            $table->string('prefix_modifier')->nullable()->comment('*, +, - for province transfer status');
            $table->string('photo_url')->nullable();
            $table->date('joining_date');
            $table->date('priesthood_date')->nullable();
            $table->date('final_vows_date')->nullable();
            $table->date('dod')->nullable();
            $table->boolean('is_active');
            $table->text('status')->nullable();
            $table->json('academic_qualifications')->nullable();
            $table->json('publications')->nullable();
            $table->json('languages')->nullable();
            $table->boolean('is_external')->default(false);
            $table->text('notes')->nullable();
            $table->string('ministry')->nullable()->comment('Current chosen ministry/apostolate');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['province_id', 'category']);
            $table->index(['region_id', 'is_active']);
            $table->index(['is_external', 'prefix_modifier']);
        });

        Schema::create('formation_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // e.g., "Novice (2 years)", "Philosophy (2 years)"
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->integer('order')->comment('Order in formation journey');
            $table->timestamps();
        });

        Schema::create('jesuit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jesuit_id')->constrained();
            $table->foreignId('community_id')->nullable()->constrained();
            $table->foreignId('province_id')->nullable()->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('assistancy_id')->nullable()->constrained();
            $table->enum('category', ['Bp', 'P', 'S', 'NS', 'F']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index(['jesuit_id', 'start_date', 'end_date']);
        });

        Schema::create('jesuit_formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jesuit_id')->constrained()->onDelete('cascade');
            $table->foreignId('formation_stage_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('current_year')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jesuit_id', 'formation_stage_id']);
            $table->index(['status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jesuit_formations');
        Schema::dropIfExists('commission_members');
        Schema::dropIfExists('jesuit_histories');
        Schema::dropIfExists('formation_stages');
        Schema::dropIfExists('jesuits');
    }
}; 