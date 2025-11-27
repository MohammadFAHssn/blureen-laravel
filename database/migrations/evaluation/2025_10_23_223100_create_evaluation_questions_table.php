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
        Schema::create('evaluation_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->nullable()->constrained('evaluation_question_categories')->restrictOnDelete();
            $table->string('question_text', 200);
            $table->foreignId('question_type_id')->constrained('question_types')->restrictOnDelete();
            $table->foreignId('evaluation_type_id')->constrained('evaluation_types')->restrictOnDelete();
            $table->unique(['category_id', 'question_text']);
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_questions');
    }
};
