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
        Schema::create('self_evaluation_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('self_evaluation_id')->constrained('self_evaluations')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->nullable();
            $table->foreignId('selected_option_id')->nullable()->constrained('evaluation_question_options')->restrictOnDelete();
            $table->string('answer_text', 512)->nullable();

            $table->unique(['self_evaluation_id', 'score']);
            $table->unique(['self_evaluation_id', 'selected_option_id'], 'sea_self_evaluation_selected_option_unique');
            $table->unique(['self_evaluation_id', 'answer_text']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_evaluation_answers');
    }
};
