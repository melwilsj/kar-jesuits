<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->foreignId('assistancy_id')
                ->after('description')
                ->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropForeign(['assistancy_id']);
            $table->dropColumn('assistancy_id');
        });
    }
}; 