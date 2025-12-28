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
        Schema::create('org_chart_nodes', function (Blueprint $table) {
            $table->id();

            // TODO: Composite indexing
            $table->foreignId('org_position_id')->constrained('org_positions')->restrictOnDelete();
            $table->foreignId('org_unit_id')->constrained('org_units')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('org_chart_nodes')->restrictOnDelete();

            $table->unique(['org_unit_id', 'org_position_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_chart_nodes');
    }
};
