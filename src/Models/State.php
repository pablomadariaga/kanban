<?php

namespace Pablomadariaga\Kanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class State
 *
 * Represents a single column (state) in a Kanban board. Each state belongs to a board
 * and contains many cards. The states within a board are ordered by a numeric position.
 *
 * @package Pablomadariaga\Kanban\Models
 *
 * @property int $id
 * @property int $board_id        ID of the board this state belongs to.
 * @property string $name         Name of the state (e.g., "To Do", "In Progress").
 * @property int $position        Position/order of this state in the board (0-indexed).
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \Pablomadariaga\Kanban\Models\Board $board
 * @property-read \Illuminate\Database\Eloquent\Collection|\Pablomadariaga\Kanban\Models\Card[] $cards
 */
class State extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'kanban_states';

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    protected $fillable = ['board_id', 'name', 'position'];

    /**
     * Get the board that this state belongs to.
     *
     * @return BelongsTo<Board, State>
     */
    public function board(): BelongsTo
    {
        $boardClass = config('kanban.models.board', Board::class);
        return $this->belongsTo($boardClass, 'board_id');
    }

    /**
     * Get the cards in this state (column).
     * Cards are ordered by their position within the state.
     *
     * @return HasMany<Card>
     */
    public function cards(): HasMany
    {
        $cardClass = config('kanban.models.card', Card::class);
        return $this->hasMany($cardClass, 'state_id')->orderBy('position');
    }
}
