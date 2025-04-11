<?php

namespace Pablomadariaga\Kanban\Events;

use Pablomadariaga\Kanban\Models\Card;
use Pablomadariaga\Kanban\Models\State;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardMoved
{
    use Dispatchable, SerializesModels;

    public Card $card;
    public State $oldState;
    public State $newState;

    public function __construct(Card $card, State $oldState, State $newState)
    {
        $this->card = $card;
        $this->oldState = $oldState;
        $this->newState = $newState;
    }
}
