<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the kanban_cards table.
 * This table stores Kanban cards, which optionally reference custom models.
 */
return new class extends Migration {
    /**
     * Run the migrations to create kanban_cards table.
     *
     * The table includes:
     * - id: Primary key.
     * - state_id: Foreign key referencing kanban_states.id.
     * - board_id: Foreign key referencing kanban_boards.id (for data integrity).
     * - position: Position of the card within its state (integer, 0-indexed).
     * - cardable_type: Class name of the related model (for polymorphic relation).
     * - cardable_id: ID of the related model (for polymorphic relation).
     * - timestamps: created_at and updated_at.
     *
     * It defines foreign keys for state_id (cascading on delete) and board_id.
     * A composite unique index ensures a given model instance appears only once per board.
     * Another unique index ensures no duplicate card positions within the same state.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('kanban_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')
                ->constrained('kanban_states')
                ->cascadeOnDelete();
            $table->foreignId('board_id')
                ->constrained('kanban_boards');
            $table->unsignedInteger('position');
            $table->string('cardable_type');
            $table->unsignedBigInteger('cardable_id');
            $table->timestamps();

            // Ensure each cardable model instance can only appear once per board.
            $table->unique(['board_id', 'cardable_type', 'cardable_id']);
            // Ensure unique position of card within a state (no two cards share the same position in one state).
            $table->unique(['state_id', 'position']);

            // Index to optimize queries filtering by state (for loading cards in a column).
            $table->index('state_id');
        });
    }

    /**
     * Reverse the migrations by dropping the kanban_cards table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_cards');
    }
};
