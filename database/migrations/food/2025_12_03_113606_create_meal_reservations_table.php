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
        Schema::create('meal_reservations', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('meal_id')->constrained('meals');
            $table->string('reserve_type');  // 'personnel', 'contractor', 'guest'
            $table->foreignId('supervisor_id')->constrained('users');
            $table->unsignedBigInteger('delivery_code');
            $table->text('description')->nullable();
            $table->string('serve_place')->nullable();
            $table->boolean('status')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('edited_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['date', 'status', 'delivery_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_reservations');
    }
};
