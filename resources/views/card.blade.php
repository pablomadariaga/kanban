{{-- Kanban Card View (Partial) --}}
{{-- This partial view renders a single card. It is included by the board view for each card. --}}
{{-- It expects a $card variable (instance of Pablomadariaga\Kanban\Models\Card) with a loaded cardable model if applicable. --}}

<li class="kanban-card overh relative flex cursor-default items-start rounded-md bg-white p-2 shadow dark:bg-stone-900"
    x-sort:item="{{ $card->id }}" wire:key="card-{{ $card->id }}">
    {{-- Drag handle for the card (only this handle is draggable, thanks to x-sort:handle). --}}
    <span class="kanban-card-handle mr-2 cursor-grab select-none" title="Drag to move card" x-sort:handle>☰</span>
    {{-- Card title (displayed from Card model's accessor) --}}
    <p class="text-xs">
        {!! $card->title !!}
    </p>
    <div class="absolute inset-0 z-10 rounded-md backdrop-blur-sm"
        wire:loading="moveCard($item, {{ $state->id }}, $position)" x-transition.duration.400ms>
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
            <svg class="h-7 w-7 animate-spin" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 3a9 9 0 1 0 9 9" />
            </svg>
        </div>
    </div>
</li>
