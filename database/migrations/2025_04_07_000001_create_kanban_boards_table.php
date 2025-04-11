<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the kanban_boards table.
 * This table stores Kanban boards, each with a name and optional description.
 */
return new class extends Migration {
    /**
     * Run the migrations to create kanban_boards table.
     *
     * The table includes:
     * - id: Primary key.
     * - name: Board name (string).
     * - description: Board description (text, nullable).
     * - timestamps: Laravel's created_at and updated_at.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('kanban_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations by dropping the kanban_boards table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_boards');
    }
};
