{{-- Kanban Card View (Partial) --}}
{{-- This partial view renders a single card. It is included by the board view for each card. --}}
{{-- It expects a $card variable (instance of Pablomadariaga\Kanban\Models\Card) with a loaded cardable model if applicable. --}}

<li class="kanban-card flex cursor-default items-center rounded-md bg-white p-2 shadow" x-sort:item="{{ $card->id }}"
    wire:key="card-{{ $card->id }}">
    {{-- Drag handle for the card (only this handle is draggable, thanks to x-sort:handle). --}}
    <span class="kanban-card-handle mr-2 select-none" title="Drag to move card" x-sort:handle>☰</span>
    {{-- Card title (displayed from Card model's accessor) --}}
    <span class="kanban-card-title">{{ $card->title }}</span>
</li>
