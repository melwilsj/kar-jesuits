<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->unique()->after('email');
            $table->foreignId('province_id')->nullable()->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('current_community_id')->nullable()->constrained('communities');
            $table->enum('type', ['Bp', 'P', 'S', 'NS', 'F'])->after('name');
            $table->boolean('is_external')->default(false);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['province_id', 'region_id', 'current_community_id']);
            $table->dropColumn([
                'phone_number',
                'province_id',
                'region_id',
                'current_community_id',
                'type',
                'is_external',
                'is_active'
            ]);
        });
    }
}; 