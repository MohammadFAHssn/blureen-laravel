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
        Schema::create('health_certificates_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('health_certificate_id');
            $table->unsignedInteger('user_id');
            $table->text('image');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('uploaded_by')->default(0);
            $table->unsignedInteger('edited_by')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table
                ->foreign('health_certificate_id', 'fk_user_certificate_id')
                ->references('id')
                ->on('health_certificates')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_certificates_users');
    }
};
