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
        Schema::create('survey_urls', function (Blueprint $table) {
            $table->id();

            $table->string('porsline_id');
            $table->foreign('porsline_id')
                ->references('porsline_id')
                ->on('surveys')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unique(['porsline_id', 'user_id']);

            $table->string('url')->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_urls');
    }
};
