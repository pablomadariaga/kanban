<?php

namespace Pablomadariaga\Kanban\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Pablomadariaga\Kanban\Models\Board;
use Pablomadariaga\Kanban\Models\State;
use Pablomadariaga\Kanban\Models\Card;

/**
 * Class KanbanBoard
 *
 * Livewire component that renders a Kanban board with drag-and-drop functionality.
 * It handles moving cards between states and updating their order.
 *
 * Usage in Blade:
 * <livewire:kanban-board :boardId="$boardId" />
 *
 * @property \Pablomadariaga\Kanban\Models\Board $board The Kanban board instance being displayed.
 */
class KanbanBoard extends Component
{
    /**
     * The ID of the board to display. Passed in during component initialization.
     * @var int
     */
    public int $boardId;

    /**
     * The Kanban board model instance.
     * @var Board
     */
    public Board $board;

    /**
     * Mount the component with the given board identifier.
     * Loads the Board and its associated States and Cards.
     *
     * @param  int  $boardId  The ID of the Kanban board to load.
     * @return void
     */
    public function mount(int $boardId): void
    {
        $this->boardId = $boardId;
        $boardClass = config('kanban.models.board', Board::class);
        // Load the board along with its states and cards (including cardable models).
        $this->board = $boardClass::with(['states.cards.cardable'])->findOrFail($boardId);
    }

    /**
     * Handles a card being moved to a new state or position.
     * This method is called via Livewire from the Alpine.js drag-and-drop event.
     *
     * @param  int  $cardId         The ID of the card being moved.
     * @param  int  $targetStateId  The ID of the state (column) where the card is dropped.
     * @param  int  $position       The new zero-based position index within the target state.
     * @return void
     */
    public function moveCard(int $cardId, int $targetStateId, int $position): void
    {
        $cardClass = config('kanban.models.card', Card::class);
        $stateClass = config('kanban.models.state', State::class);

        /** @var Card $card */
        $card = $cardClass::findOrFail($cardId);
        $currentStateId = $card->state_id;

        // Prevent moving cards to another board.
        if ($card->board_id !== $this->board->id) {
            return;
        }

        DB::transaction(function () use ($card, $currentStateId, $targetStateId, $position, $stateClass) {
            // Retrieve the card collections of the source and target states.
            $sourceState = $stateClass::find($currentStateId);
            $sourceStateCards = $sourceState->cards()->orderBy('position')->get();

            if ($currentStateId === $targetStateId) {
                $targetStateCards = $sourceStateCards;
            } else {
                $targetState = $stateClass::find($targetStateId);
                $targetStateCards = $targetState->cards()->orderBy('position')->get();
            }

            // Remove the card being moved from the source state's collection.
            $sourceIndex = $sourceStateCards->search(fn($c) => $c->id === $card->id);
            if ($sourceIndex !== false) {
                $sourceStateCards->splice($sourceIndex, 1);
            }

            // When moving to a different state, ensure the card is not already present in the target collection.
            if ($currentStateId !== $targetStateId) {
                $targetStateCards = $targetStateCards->filter(fn($c) => $c->id !== $card->id);
            }

            // Update the card's state_id (for different state moves).
            $card->state_id = $targetStateId;
            // The state_id update will be applied via the bulk update query below.

            // Insert the card into the target collection at the specified position.
            $position = max(0, $position);
            $position = ($position > $targetStateCards->count()) ? $targetStateCards->count() : $position;
            $targetStateCards->splice($position, 0, [$card]);

            if ($currentStateId === $targetStateId) {
                // Reordering within the same state: update the target collection.
                $this->bulkUpdatePositions($targetStateCards);
            } else {
                // When moving between different states, perform a bulk update on both collections.
                $this->bulkUpdatePositions($sourceStateCards);
                // For the target state, also update the state_id of the moved card.
                $this->bulkUpdatePositions($targetStateCards, true, $card->id, $targetStateId);
            }
        });

        // Refresh the board to reflect the updated positions.
        $boardClass = config('kanban.models.board', Board::class);
        $this->board = $boardClass::with(['states.cards.cardable'])->find($this->board->id);
        $this->dispatch('card-moved', cardId: $cardId);
    }

    /**
     * Performs a bulk update of card positions using an SQL query with a CASE statement.
     *
     * @param \Illuminate\Support\Collection $cards Collection of cards to update.
     * @param bool $updateStateId Indicates whether to also update the state_id (for the moved card).
     * @param int|null $movedCardId ID of the moved card.
     * @param int|null $targetStateId New state_id for the moved card.
     * @return void
     */
    private function bulkUpdatePositions($cards, bool $updateStateId = false, ?int $movedCardId = null, ?int $targetStateId = null): void
    {
        if ($cards->isEmpty()) {
            return;
        }

        $ids = $cards->pluck('id')->toArray();
        $casesPosition = '';
        $casesStateId = '';

        foreach ($cards as $index => $card) {
            $casesPosition .= " WHEN id = {$card->id} THEN {$index} ";
            if ($updateStateId && $movedCardId !== null && $targetStateId !== null) {
                if ($card->id === $movedCardId) {
                    $casesStateId .= " WHEN id = {$card->id} THEN {$targetStateId} ";
                }
            }
        }

        $idsList = implode(',', $ids);
        $sql = "UPDATE kanban_cards SET position = CASE {$casesPosition} END";
        if ($updateStateId) {
            $sql .= ", state_id = CASE {$casesStateId} ELSE state_id END";
        }
        $sql .= " WHERE id IN ({$idsList})";
        DB::update($sql);
    }

    /**
     * Render the Kanban board view.
     * This uses the Blade template provided by the package to display the board.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('kanban::board', [
            'board' => $this->board,
        ]);
    }
}
