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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unique(['month', 'year']);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->boolean('active')->default(false);
            $table->boolean('sms_sent')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
