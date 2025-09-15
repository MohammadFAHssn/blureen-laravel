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
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();

            $table->morphs('approvable');

            $table->unsignedBigInteger('requester_user_id')->nullable();
            $table->foreign('requester_user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->unsignedBigInteger('requester_position_id')->nullable();
            $table->foreign('requester_position_id')
                ->references('rayvarz_id')
                ->on('job_positions')
                ->restrictOnDelete();

            $table->unsignedBigInteger('approver_user_id')->nullable();
            $table->foreign('approver_user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();


            $table->unsignedBigInteger('approver_position_id')->nullable();
            $table->foreign('approver_position_id')
                ->references('rayvarz_id')
                ->on('job_positions')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('priority')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flows');
    }
};
