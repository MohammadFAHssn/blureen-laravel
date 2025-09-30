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
        Schema::create('birthday_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('month');
            $table->string('year');
            $table->boolean('status');
            $table->unsignedInteger('uploaded_by');
            $table->unsignedInteger('edited_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthday_files');
    }
};
