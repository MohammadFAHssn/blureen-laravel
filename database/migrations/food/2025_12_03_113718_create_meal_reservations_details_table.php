<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meal_reservations_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserved_for_personnel')->constrained('users');
            $table->foreignId('reserved_for_contractor')->constrained('contractors');
            $table->foreignId('food_id')->constrained('foods');
            $table->unsignedBigInteger('food_price');
            $table->string('reserve_type');  // 'personnel', 'contractor', 'guest'
            $table->foreignId('supervisor_id')->constrained('users');
            $table->unsignedBigInteger('delivery_code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('edited_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_reservations_details');
    }
};
