<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistancy_id')->constrained();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('country')->nullable()->default('India');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('country')->nullable()->default('India');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('province_id')->nullable()->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('assistancy_id')->nullable()->constrained();
            $table->foreignId('parent_community_id')->nullable()
                ->references('id')->on('communities');
            $table->enum('superior_type', ['Superior', 'Rector', 'Coordinator']);
            $table->string('address');
            $table->string('diocese')->nullable();
            $table->string('taluk')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable()->default('India');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_formation_house')->default(false);
            $table->boolean('is_common_house')->default(false);
            $table->boolean('is_attached_house')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add the check constraints using raw SQL after table creation
        DB::statement("ALTER TABLE communities ADD CONSTRAINT chk_attached_house_coordinator 
            CHECK ((is_attached_house = true AND superior_type = 'Coordinator') OR (is_attached_house = false))");

        DB::statement("ALTER TABLE communities ADD CONSTRAINT chk_community_hierarchy 
            CHECK ((province_id IS NOT NULL AND assistancy_id IS NULL) OR 
                   (province_id IS NULL AND assistancy_id IS NOT NULL) OR 
                   (province_id IS NULL AND assistancy_id IS NULL AND parent_community_id IS NOT NULL))");

        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('community_id')->constrained();
            $table->enum('type', [
                'school', 'college', 'university', 'hostel', 
                'community_college', 'iti', 'parish', 
                'social_centre', 'farm', 'ngo', 'retreat_center', 'other'
            ]);
            $table->string('diocese')->nullable();
            $table->string('taluk')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->text('address');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
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
            $table->softDeletes();

            $table->index(['community_id', 'type']);
        });

        // Commissions
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('province_id')->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
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