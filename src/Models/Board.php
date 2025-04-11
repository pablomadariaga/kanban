<?php

namespace Pablomadariaga\Kanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Board
 *
 * Represents a Kanban board, which contains multiple states (columns).
 * Users can associate custom models (cardable entities) to cards on a board.
 *
 * @package Pablomadariaga\Kanban\Models
 *
 * @property int $id
 * @property string $name           Name of the board.
 * @property string|null $description  Optional description of the board.
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Pablomadariaga\Kanban\Models\State[] $states
 * @property-read \Illuminate\Database\Eloquent\Collection|\Pablomadariaga\Kanban\Models\Card[] $cards
 */
class Board extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Using a custom table name to avoid conflicts with any user tables.
     * @var string
     */
    protected $table = 'kanban_boards';

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    protected $fillable = ['name', 'description'];

    /**
     * Get the states (columns) associated with this board.
     * States are ordered by their position in the board.
     *
     * @return HasMany<State>
     */
    public function states(): HasMany
    {
        $stateClass = config('kanban.models.state', State::class);
        return $this->hasMany($stateClass, 'board_id')->orderBy('position');
    }

    /**
     * Get all cards on this board (through its states).
     * This provides a direct access to Card models associated with the board.
     *
     * @return HasMany<Card>
     */
    public function cards(): HasMany
    {
        $cardClass = config('kanban.models.card', Card::class);
        return $this->hasMany($cardClass, 'board_id');
    }
}
