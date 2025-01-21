<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        });

        Schema::create('document_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_access');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
    }
}; 