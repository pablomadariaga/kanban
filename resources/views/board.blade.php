{{-- Kanban Board Main View --}}
{{-- This view renders a full Kanban board with its states and cards. --}}
{{-- It expects a $board variable (an instance of Pablomadariaga\Kanban\Models\Board) with loaded states and cards. --}}

<div class="kanban-board flex space-x-4 overflow-x-auto p-4">
    @foreach ($board->states as $state)
        <div class="kanban-column w-64 flex-shrink-0 rounded-md bg-gray-100">
            {{-- State Column Header --}}
            <div class="kanban-column-header px-3 py-2 font-bold text-gray-700">
                {{ $state->name }}
            </div>
            {{-- Cards List --}}
            <ul class="kanban-cards flex flex-col gap-2 px-2 pb-2" x-data {{-- Initialize Alpine.js context for this column (not strictly needed for x-sort, but can be used if extended) --}}
                x-sort.ghost="$wire.moveCard($item, {{ $state->id }}, $position)"
                x-sort:group="'board-{{ $board->id }}'" wire:key="state-{{ $state->id }}">
                @foreach ($state->cards as $card)
                    @include('kanban::card', ['card' => $card])
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
