<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('commission_user', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->foreignId('commission_role_id')->constrained('commission_roles');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->json('metadata')->nullable(); // For additional group-specific data
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        // Reverse the changes
    }
}; 