<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistancies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('common_houses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('assistancy_id')->constrained();
            $table->text('address');
            $table->json('contact_details')->nullable()->comment('Array of phones, emails, fax, website');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistancy_id')->constrained();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('province_id')->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('parent_community_id')->nullable()->constrained('communities');
            $table->enum('superior_type', ['rector', 'superior', 'coordinator'])->nullable();
            $table->text('address');
            $table->string('diocese')->nullable();
            $table->string('taluk')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_formation_house')->default(false);
            $table->boolean('is_attached_house')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('community_id')->constrained();
            $table->enum('type', [
                'school', 'college', 'university', 'hostel', 
                'community_college', 'iti', 'parish', 
                'social_centre', 'farm', 'ngo', 'other'
            ]);
            $table->text('description')->nullable();
            $table->json('contact_details')->comment('Array of phones, emails, fax, website');
            $table->json('student_demographics')->nullable()->comment('
                {
                    "catholics": integer,
                    "other_christians": integer,
                    "non_christians": integer,
                    "boys": integer,
                    "girls": integer,
                    "total": integer
                }
            ');
            $table->json('staff_demographics')->comment('
                {
                    "jesuits": integer,
                    "other_religious": integer,
                    "catholics": integer,
                    "others": integer,
                    "total": integer
                }
            ');
            $table->json('beneficiaries')->nullable()->comment('For social centres and parishes');
            $table->string('diocese')->nullable();
            $table->string('taluk')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->text('address');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['community_id', 'type']);
        });

        // Commissions
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('province_id')->constrained();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('communities');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('common_houses');
        Schema::dropIfExists('assistancies');
    }
}; 