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
        $cardClass = config('kanban.models.card', Card::class);
        $stateClass = config('kanban.models.state', State::class);

        /** @var Card $card */
        $card = $cardClass::findOrFail($cardId);
        $currentStateId = $card->state_id;

        // Prevenir mover tarjetas a otro board
        if ($card->board_id !== $this->board->id) {
            return;
        }

        DB::transaction(function () use ($card, $currentStateId, $targetStateId, $position, $stateClass) {
            // Obtener las colecciones de tarjetas actuales de source y target.
            $sourceState = $stateClass::find($currentStateId);
            $sourceStateCards = $sourceState->cards()->orderBy('position')->get();

            if ($currentStateId === $targetStateId) {
                $targetStateCards = $sourceStateCards;
            } else {
                $targetState = $stateClass::find($targetStateId);
                $targetStateCards = $targetState->cards()->orderBy('position')->get();
            }

            // Remover la tarjeta que se está moviendo de la colección del estado origen.
            $sourceIndex = $sourceStateCards->search(fn($c) => $c->id === $card->id);
            if ($sourceIndex !== false) {
                $sourceStateCards->splice($sourceIndex, 1);
            }

            // Si se mueve a un estado distinto, asegurarse de que no esté ya presente en la colección destino.
            if ($currentStateId !== $targetStateId) {
                $targetStateCards = $targetStateCards->filter(fn($c) => $c->id !== $card->id);
            }

            // Actualizar el state_id de la tarjeta (para el caso de estados diferentes).
            $card->state_id = $targetStateId;
            // La actualización de state_id se aplicará mediante la consulta masiva en el grupo destino.

            // Insertar la tarjeta en la colección destino en la posición indicada.
            $position = max(0, $position);
            $position = ($position > $targetStateCards->count()) ? $targetStateCards->count() : $position;
            $targetStateCards->splice($position, 0, [$card]);

            if ($currentStateId === $targetStateId) {
                // Reordenamiento dentro del mismo estado: actualizamos la colección destino.
                $this->bulkUpdatePositions($targetStateCards);
            } else {
                // En estados diferentes: actualizamos masivamente ambas colecciones.
                $this->bulkUpdatePositions($sourceStateCards);
                // En la actualización destino, actualizamos además el state_id de la tarjeta movida.
                $this->bulkUpdatePositions($targetStateCards, true, $card->id, $targetStateId);
            }
        });

        // Refrescar el board para reflejar el cambio.
        $boardClass = config('kanban.models.board', Board::class);
        $this->board = $boardClass::with(['states.cards.cardable'])->find($this->board->id);
        $this->dispatch('card-moved', cardId: $cardId);
    }

    /**
     * Actualiza masivamente las posiciones de las tarjetas usando una consulta SQL con CASE.
     *
     * @param \Illuminate\Support\Collection $cards Colección de tarjetas a actualizar.
     * @param bool $updateStateId Indica si se debe actualizar también el state_id (para la tarjeta movida).
     * @param int|null $movedCardId ID de la tarjeta movida.
     * @param int|null $targetStateId Nuevo state_id para la tarjeta movida.
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
