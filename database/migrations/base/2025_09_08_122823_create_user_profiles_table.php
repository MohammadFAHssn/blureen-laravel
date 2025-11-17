<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('national_code')->nullable()->unique();
            $table->string('gender')->nullable();
            $table->string('father_name')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('employment_date')->nullable();
            $table->string('start_date')->nullable();

            $table->unsignedBigInteger('education_level_id')->nullable();
            $table->foreign('education_level_id')
                ->references('rayvarz_id')
                ->on('education_levels')
                ->nullOnDelete();

            $table->unsignedBigInteger('workplace_id')->nullable();
            $table->foreign('workplace_id')
                ->references('rayvarz_id')
                ->on('workplaces')
                ->nullOnDelete();

            $table->unsignedBigInteger('work_area_id')->nullable();
            $table->foreign('work_area_id')
                ->references('rayvarz_id')
                ->on('work_areas')
                ->nullOnDelete();

            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->foreign('cost_center_id')
                ->references('rayvarz_id')
                ->on('cost_centers')
                ->nullOnDelete();

            $table->unsignedBigInteger('job_position_id')->nullable();
            $table->foreign('job_position_id')
                ->references('rayvarz_id')
                ->on('job_positions')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
