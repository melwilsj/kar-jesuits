<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {     
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['regular', 'special']);
            $table->string('event_type')->nullable(); // birthday, jubilee, seminar, etc.
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime')->nullable();
            $table->string('venue')->nullable();
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('jesuit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('community_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // yearly, monthly, etc.
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('event_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'document']);
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['event', 'news', 'announcement', 'birthday', 'feast_day', 'death', 'other']);
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->json('metadata')->nullable(); // For additional data related to notifications
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('read_at');
            $table->timestamps();
            
            // Add unique constraint to prevent duplicates
            $table->unique(['notification_id', 'user_id']);
        });

        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->enum('recipient_type', ['user', 'province', 'region', 'community', 'all']);
            $table->unsignedBigInteger('recipient_id')->nullable(); // ID of the user, province, region, or community
            $table->timestamps();
        });        
    }

    public function down()
    {
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notification_reads');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('event_attachments');
        Schema::dropIfExists('events');
    }
};