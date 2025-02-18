<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Document Management
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('allowed_file_types')->nullable();
            $table->boolean('is_private')->default(true);
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('category_id')->constrained('document_categories');
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->boolean('is_private')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained();
            $table->timestamps();
        });

        // External Assignments
        Schema::create('external_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jesuit_id')->constrained();
            $table->morphs('assignable'); // For common_houses or external institutions
            $table->enum('assignment_type', ['studies', 'work', 'sabbatical']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jesuit_id', 'assignable_type', 'assignable_id']);
            $table->index(['is_active', 'end_date']);
        });

        // Province Transfers
        Schema::create('province_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jesuit_id')->constrained();
            $table->foreignId('from_province_id')->constrained('provinces');
            $table->foreignId('to_province_id')->constrained('provinces');
            $table->foreignId('requested_by')->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed']);
            $table->date('request_date');
            $table->date('effective_date')->nullable();
            $table->date('processed_date')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jesuit_id', 'status']);
            $table->index(['from_province_id', 'to_province_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('province_transfers');
        Schema::dropIfExists('external_assignments');
        Schema::dropIfExists('document_access');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
    }
}; 