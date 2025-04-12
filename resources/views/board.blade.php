{{-- Kanban Board Main View --}}
{{-- This view renders a full Kanban board with its states and cards. --}}
{{-- It expects a $board variable (an instance of Pablomadariaga\Kanban\Models\Board) with loaded states and cards. --}}

<div class="flex gap-4 overflow-x-auto p-4">
    @foreach ($board->states as $state)
        <div class="relative w-64 flex-shrink-0 rounded-xl bg-stone-300/30 shadow-sm dark:bg-stone-800/30">
            {{-- State Column Header --}}
            <div
                class="flex items-center justify-between rounded-t-xl bg-stone-400/30 px-3 py-2 text-sm font-semibold text-stone-800 dark:bg-stone-950/50 dark:text-stone-100">
                <span>
                    {{ $state->name }}
                </span>
                <span class="block h-3 w-3 rounded-full border-2 border-white text-2xl leading-none shadow-sm"
                    style="background-color: {{ $state->color ?? '#000' }}">
                </span>
            </div>
            {{-- Cards List --}}
            <ul class="flex h-[calc(100%-2.25rem)] flex-col gap-2 px-2 py-2" x-sort
                x-sort.ghost="$wire.moveCard($item, {{ $state->id }}, $position)"
                x-sort:group="'board-{{ $board->id }}'" wire:key="state-{{ $state->id }}">
                @foreach ($state->cards as $card)
                    @include('kanban::card', ['card' => $card])
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
