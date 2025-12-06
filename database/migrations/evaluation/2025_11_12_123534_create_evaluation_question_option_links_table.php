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
        Schema::create('evaluation_question_option_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')->constrained('evaluation_questions')->restrictOnDelete();
            $table->foreignId('option_id')->constrained('evaluation_question_options')->restrictOnDelete();
            $table->unique(['question_id', 'option_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_question_option_links');
    }
};
