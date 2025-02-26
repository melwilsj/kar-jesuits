<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->json('data');
            $table->enum('action', ['update', 'delete']);
            $table->foreignId('changed_by_id')->nullable()->constrained('users');
            $table->timestamp('snapshot_time');
            $table->timestamps();

            $table->index(['model_type', 'model_id', 'snapshot_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_snapshots');
    }
}; 