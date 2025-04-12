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
 * @property \Pablomadariaga\Kanban\Models\Board $board  The Kanban board instance being displayed.
 */
class KanbanBoard extends Component
{
    /**
     * The ID of the board to display. Passed in from the component initialization.
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
     * Loads the Board and its related States and Cards.
     *
     * @param  int  $boardId  The ID of the Kanban board to load.
     * @return void
     */
    public function mount(int $boardId): void
    {
        $this->boardId = $boardId;
        $boardClass = config('kanban.models.board', Board::class);
        // Load the board along with its states and cards (and cardable models).
        $this->board = $boardClass::with(['states.cards.cardable'])->findOrFail($boardId);
    }

    /**
     * Handle a card being moved to a new state or position.
     * This method is called via Livewire from the Alpine.js drag-and-drop event.
     *
     * @param  int  $cardId         The ID of the card being moved.
     * @param  int  $targetStateId  The ID of the state (column) where the card is dropped.
     * @param  int  $position       The new zero-based position index within the target state.
     * @return void
     */
    public function moveCard(int $cardId, int $targetStateId, int $position): void
    {
        // Load the card and determine its current state.
        $cardClass = config('kanban.models.card', Card::class);
        $stateClass = config('kanban.models.state', State::class);

        /** @var Card $card */
        $card = $cardClass::findOrFail($cardId);
        $currentStateId = $card->state_id;

        // If the card is not on the same board as this component's board, abort for safety.
        if ($card->board_id !== $this->board->id) {
            return; // Prevent moving cards to a different board (ignore the action).
        }

        // If the card's state is unchanged and position is unchanged, no action needed.
        if ($currentStateId == $targetStateId) {
            // We still handle reordering within the same state.
        }

        // Perform the reordering in a transaction to ensure consistency.
        DB::transaction(function () use ($card, $currentStateId, $targetStateId, $position, $stateClass) {
            // Get the collection of cards in the source state (and target state if different).
            $sourceStateCards = $stateClass::find($currentStateId)
                ->cards()
                ->orderBy('position')
                ->get();
            $targetStateCards = ($currentStateId === $targetStateId)
                ? $sourceStateCards
                : $stateClass::find($targetStateId)
                ->cards()
                ->orderBy('position')
                ->get();

            // Remove the card from the source state list
            $sourceIndex = $sourceStateCards->search(fn($c) => $c->id === $card->id);
            if ($sourceIndex !== false) {
                $sourceStateCards->splice($sourceIndex, 1);
            }

            // If moving to a different state, also remove it from the target list if somehow present (shouldn't be).
            if ($currentStateId !== $targetStateId) {
                $targetStateCards = $targetStateCards->filter(fn($c) => $c->id !== $card->id);
            }

            // Update the card's state to the new state.
            $card->state_id = $targetStateId;

            // Insert the card into the target state's collection at the specified position.
            $position = max(0, $position); // ensure position is not negative
            $position = ($position > $targetStateCards->count())
                ? $targetStateCards->count()
                : $position;
            $targetStateCards->splice($position, 0, [$card]);

            // Recalculate and persist positions for cards in the source state.
            if ($currentStateId === $targetStateId) {
                // Same state reordering: $sourceStateCards and $targetStateCards are the same reference.
                $updatedCards = $targetStateCards;
            } else {
                // Different state: Update source state card positions (closing the gap).
                $index = 0;
                foreach ($sourceStateCards as $c) {
                    $c->position = $index++;
                    $c->save();
                }
                // Prepare to update target state cards (including the moved card).
                $updatedCards = $targetStateCards;
            }

            // Save the new positions in the target state (and the moved card's new state).
            $index = 0;
            foreach ($updatedCards as $c) {
                $c->position = $index++;
                $c->save();
            }
        });

        // Refresh the board and its relations to reflect the updated positions.
        $boardClass = config('kanban.models.board', Board::class);
        $this->board = $boardClass::with(['states.cards.cardable'])->find($this->board->id);
        $this->dispatch('card-moved', cardId: $cardId);
    }

    /**
     * Render the Kanban board view.
     * This will use the Blade template provided by the package to display the board.
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
