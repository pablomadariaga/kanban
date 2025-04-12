<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the kanban_states table.
 * This table stores the states (columns) for Kanban boards.
 */
return new class extends Migration {
    /**
     * Run the migrations to create kanban_states table.
     *
     * The table includes:
     * - id: Primary key.
     * - board_id: Foreign key referencing kanban_boards.id.
     * - name: State name (string).
     * - position: Position of the state in the board (integer, 0-indexed).
     * - timestamps: created_at and updated_at.
     *
     * Also sets up a foreign key constraint on board_id with cascade on delete,
     * so deleting a Board will remove its States automatically.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('kanban_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')
                ->constrained('kanban_boards')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default("#000");
            $table->unsignedInteger('position');
            $table->timestamps();

            // Index for quick lookup of states by board and position.
            $table->index(['board_id', 'position']);
        });
    }

    /**
     * Reverse the migrations by dropping the kanban_states table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_states');
    }
};
