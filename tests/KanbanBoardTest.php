<?php

use Pablomadariaga\Kanban\Models\Board;
use Pablomadariaga\Kanban\Models\State;
use Pablomadariaga\Kanban\Models\Card;
use Pablomadariaga\Kanban\Livewire\KanbanBoard;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Pablomadariaga\Kanban\Events\CardMoved;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->board = Board::factory()->create(['name' => 'Main Board']);
    $this->todo = State::factory()->create(['board_id' => $this->board->id, 'name' => 'Todo', 'position' => 0]);
    $this->done = State::factory()->create(['board_id' => $this->board->id, 'name' => 'Done', 'position' => 1]);

    $this->card = Card::factory()->create([
        'board_id' => $this->board->id,
        'state_id' => $this->todo->id,
        'position' => 0,
        'cardable_type' => 'App\\Models\\Task',
        'cardable_id' => 1,
    ]);
});

it('renders the kanban board correctly', function () {
    Livewire::test(KanbanBoard::class, ['boardId' => $this->board->id])
        ->assertSee($this->todo->name)
        ->assertSee($this->done->name)
        ->assertSee($this->card->title);
});

it('moves a card between states', function () {
    Livewire::test(KanbanBoard::class, ['boardId' => $this->board->id])
        ->call('moveCard', $this->card->id, $this->done->id, 0);

    $this->card->refresh();

    expect($this->card->state_id)->toBe($this->done->id)
        ->and($this->card->position)->toBe(0);
});

it('fires the CardMoved event when moving a card', function () {
    Event::fake();

    Livewire::test(KanbanBoard::class, ['boardId' => $this->board->id])
        ->call('moveCard', $this->card->id, $this->done->id, 0);

    Event::assertDispatched(CardMoved::class, function ($event) {
        return $event->card->id === $this->card->id
            && $event->oldState->id === $this->todo->id
            && $event->newState->id === $this->done->id;
    });
});
