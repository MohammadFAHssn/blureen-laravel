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
        Schema::create('request_approval_rules', function (Blueprint $table) {
            $table->id();

            // TODO: Composite indexing
            $table->foreignId('request_type_id')->constrained('request_types')->restrictOnDelete();
            $table->foreignId('requester_org_position_id')->constrained('org_positions')->restrictOnDelete();
            $table->foreignId('approver_org_position_id')->nullable()->constrained('org_positions')->restrictOnDelete();
            $table->foreignId('approver_org_chart_node_id')->nullable()->constrained('org_chart_nodes')->restrictOnDelete();
            $table->unsignedTinyInteger('priority')->default(1);

            $table->unique([
                'request_type_id',
                'requester_org_position_id',
                'approver_org_position_id',
            ], 'rar_req_type_req_org_pos_approver_org_pos_unique');

            $table->unique([
                'request_type_id',
                'requester_org_position_id',
                'approver_org_chart_node_id',
            ], 'rar_req_type_req_org_pos_approver_org_chart_unique');

            $table->unique([
                'request_type_id',
                'requester_org_position_id',
                'priority',
            ], 'rar_req_type_req_org_pos_priority_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_approval_rules');
    }
};
