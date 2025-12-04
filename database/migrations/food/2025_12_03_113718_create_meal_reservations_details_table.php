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
            $table->foreignId('meal_reservation_id')->constrained('meal_reservations')->cascadeOnDelete();
            $table->foreignId('reserved_for_personnel')->nullable()->constrained('users');
            $table->foreignId('reserved_for_contractor')->nullable()->constrained('contractors');
            $table->foreignId('food_id')->constrained('foods');
            $table->unsignedBigInteger('food_price');
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('delivery_status')->default(0);
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
