{{-- Kanban Board Main View --}}
{{-- This view renders a full Kanban board with its states and cards. --}}
{{-- It expects a $board variable (an instance of Pablomadariaga\Kanban\Models\Board) with loaded states and cards. --}}

<div class="flex gap-4 overflow-x-auto p-4">
    @foreach ($board->states as $state)
        <div class="lex-shrink-0 w-64 rounded-xl bg-stone-300/70 dark:bg-stone-800/80">
            {{-- State Column Header --}}
            <div
                class="rounded-t-xl bg-stone-400/70 px-3 py-2 text-sm font-semibold text-stone-800 dark:bg-stone-950/60 dark:text-stone-100">
                {{ $state->name }}
            </div>
            {{-- Cards List --}}
            <ul class="flex flex-col gap-2 px-2 pb-2" x-data {{-- Initialize Alpine.js context for this column (not strictly needed for x-sort, but can be used if extended) --}}
                x-sort.ghost="$wire.moveCard($item, {{ $state->id }}, $position)"
                x-sort:group="'board-{{ $board->id }}'" wire:key="state-{{ $state->id }}">
                @foreach ($state->cards as $card)
                    @include('kanban::card', ['card' => $card])
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
