<?php

namespace Pablomadariaga\Kanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Card
 *
 * Represents a Kanban card, which is an item in a board column (state).
 * Each card may optionally reference a "cardable" model (any Eloquent model) that holds the actual content.
 * Cards are ordered within their state and can be moved between states.
 *
 * @package Pablomadariaga\Kanban\Models
 *
 * @property int $id
 * @property int $board_id            ID of the board this card belongs to.
 * @property int $state_id            ID of the state (column) this card is currently in.
 * @property int $position            Position/order of the card within its state (0-indexed).
 * @property string $cardable_type    The class name of the related model (if any).
 * @property int $cardable_id         The primary key of the related model (if any).
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \Pablomadariaga\Kanban\Models\Board $board
 * @property-read \Pablomadariaga\Kanban\Models\State $state
 * @property-read Model|null $cardable  The associated custom model instance (polymorphic relation).
 * @property-read string $title        A computed title for this card, typically derived from the cardable model.
 */
class Card extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'kanban_cards';

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    protected $fillable = ['board_id', 'state_id', 'position', 'cardable_id', 'cardable_type'];

    /**
     * Get the board that this card belongs to.
     * (Note: The board is also accessible via $card->state->board.)
     *
     * @return BelongsTo<Board, Card>
     */
    public function board(): BelongsTo
    {
        $boardClass = config('kanban.models.board', Board::class);
        return $this->belongsTo($boardClass, 'board_id');
    }

    /**
     * Get the state (column) that this card belongs to.
     *
     * @return BelongsTo<State, Card>
     */
    public function state(): BelongsTo
    {
        $stateClass = config('kanban.models.state', State::class);
        return $this->belongsTo($stateClass, 'state_id');
    }

    /**
     * Get the related model for this card (polymorphic relationship).
     * This allows the card to reference any Eloquent model in the application (e.g., a Task, Issue, etc.).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function cardable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessor for the card's display title.
     * Attempts to retrieve a human-friendly title from the related cardable model.
     * By default, it uses the attribute configured in 'kanban.card_title_attribute' (e.g., 'title').
     * Falls back to the model's __toString() or a generic "ModelName #id" if a title attribute is not available.
     *
     * @return string
     */
    public function getTitleAttribute(): string
    {
        // If there's no related model, use a generic label with the card ID.
        if (!$this->cardable) {
            return 'Card #' . $this->id;
        }

        $model = $this->cardable;
        // Attribute name to display (default to 'title', configurable via config).
        $titleAttribute = config('kanban.card_title_attribute', 'title');
        $value = null;
        if ($model instanceof Model) {
            $value = $model->getAttribute($titleAttribute);
        }
        // Use the configured title attribute if available and not null/empty.
        if (is_string($value) && strlen($value) > 0) {
            return $value;
        }
        // If the model has a __toString method, use its string representation.
        if (method_exists($model, '__toString')) {
            return (string) $model;
        }
        // Fallback to a generic "ModelName #id".
        $modelName = class_basename($model);
        $modelId = $model instanceof Model ? $model->getKey() : $this->cardable_id;
        return $modelName . ' #' . $modelId;
    }
}
