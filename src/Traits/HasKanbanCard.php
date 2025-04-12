<?php

namespace Pablomadariaga\Kanban\Traits;

use Pablomadariaga\Kanban\Models\Card;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * Trait HasKanbanCard
 *
 * This trait allows an Eloquent model to be linked as a "cardable" entity in the Kanban system.
 * By using this trait, the model will have a polymorphic one-to-one relationship with the Card model.
 *
 * Usage Example:
 *
 * ```php
 * namespace App\Models;
 *
 * use Illuminate\Database\Eloquent\Model;
 * use Pablomadariaga\Kanban\Traits\HasKanbanCard;
 *
 * class Task extends Model
 * {
 *     use HasKanbanCard;
 *
 *     // Additional properties and methods
 * }
 * ```
 *
 * With this trait, you can easily access the related Kanban card using:
 *
 * ```php
 * $task = Task::find(1);
 * $kanbanCard = $task->kanbanCard; // Returns the associated card instance or null if none.
 * ```
 *
 * @package Pablomadariaga\Kanban\Traits
 */
trait HasKanbanCard
{
    /**
     * Define a polymorphic one-to-one relationship.
     *
     * This method sets up the relationship linking the current model (as cardable) with a Card.
     * It uses the columns "cardable_type" and "cardable_id" in the cards table.
     *
     * @return MorphMany<Card> Returns the associated Card model instance.
     */
    public function kanbanCard(): MorphMany
    {
        return $this->morphMany(Card::class, 'cardable');
    }


    public static function createCard(array $attributes)
    {
        return DB::transaction(function () use ($attributes) {
            $cardClass = config('kanban.models.card', Card::class);
            // Updates all cards in the state by adding 1 to their position.
            // This moves all cards one position forward and avoids conflicts with the uniqueness constraint.
            $cardClass::where('state_id', $attributes['state_id'])
                ->where('board_id', $attributes['board_id'])
                ->increment('position');

            // Assign position 0 to the new card.
            $attributes['position'] = 0;
            return $cardClass::create($attributes);
        });
    }
}
