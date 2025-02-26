<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

return new class extends Migration
{
    public function up(): void
    {
        // First, deactivate any duplicate active roles
        DB::statement("
            UPDATE role_assignments ra1
            SET is_active = false,
                end_date = CURRENT_DATE
            WHERE EXISTS (
                SELECT 1
                FROM role_assignments ra2
                WHERE ra2.assignable_type = ra1.assignable_type
                AND ra2.assignable_id = ra1.assignable_id
                AND ra2.role_type_id = ra1.role_type_id
                AND ra2.is_active = true
                AND ra2.id > ra1.id
            )
        ");

        // Add unique constraint for active roles
        Schema::table('role_assignments', function (Blueprint $table) {
            // One active role of each type per assignable entity
            $table->unique(
                ['assignable_type', 'assignable_id', 'role_type_id', 'is_active'],
                'unique_active_role_per_entity'
            );
        });
    }

    public function down(): void
    {
        Schema::table('role_assignments', function (Blueprint $table) {
            $table->dropUnique('unique_active_role_per_entity');
        });
    }
}; 