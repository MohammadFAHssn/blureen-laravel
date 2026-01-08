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
        Schema::create('files', function (Blueprint $table) {
            $table->id();

            $table->string('original_name');
            $table->string('stored_name');
            $table->text('path');
            $table->string('disk')->default('local');
            $table->string('mime_type');
            $table->string('extension');
            $table->unsignedBigInteger('size');

            $table->nullableMorphs('fileable');

            $table->string('collection')->default('default')->index();
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->json('metadata')->nullable();

            $table->enum('visibility', ['public', 'private'])->default('private');
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();

            $table->unsignedTinyInteger('order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['fileable_type', 'fileable_id', 'collection']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
