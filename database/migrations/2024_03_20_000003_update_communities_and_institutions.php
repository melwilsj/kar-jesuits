<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->enum('superior_type', ['rector', 'superior'])->nullable();
            $table->string('diocese')->nullable();
            $table->string('taluk')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->boolean('is_attached_house')->default(false);
            $table->foreignId('parent_community_id')->nullable()->constrained('communities');
            $table->foreignId('coordinator_id')->nullable()->constrained('users');
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->enum('type', [
                'school', 'college', 'university', 'hostel', 
                'community_college', 'iti', 'parish', 
                'social_centre', 'farm', 'ngo', 'other'
            ]);
            $table->json('contact_details');
            $table->json('student_demographics')->nullable();
            $table->json('staff_demographics');
            $table->string('diocese')->nullable();
            $table->string('taluk')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        // Reverse the changes
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn([
                'superior_type', 'diocese', 'taluk', 'district', 'state',
                'is_attached_house', 'parent_community_id', 'coordinator_id'
            ]);
        });
    }
}; 