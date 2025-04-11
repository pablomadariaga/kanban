{{-- Kanban Board Main View --}}
{{-- This view renders a full Kanban board with its states and cards. --}}
{{-- It expects a $board variable (an instance of Pablomadariaga\Kanban\Models\Board) with loaded states and cards. --}}

<div class="kanban-board">
    @foreach ($board->states as $state)
        <div class="kanban-column">
            {{-- State Column Header --}}
            <div class="kanban-column-header">
                {{ $state->name }}
            </div>
            {{-- Cards List --}}
            <ul class="kanban-cards" x-data {{-- Initialize Alpine.js context for this column (not strictly needed for x-sort, but can be used if extended) --}}
                x-sort.ghost="$wire.moveCard($item, {{ $state->id }}, $position)"
                x-sort:group="'board-{{ $board->id }}'" wire:key="state-{{ $state->id }}">
                @foreach ($state->cards as $card)
                    @include('kanban::card', ['card' => $card])
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
