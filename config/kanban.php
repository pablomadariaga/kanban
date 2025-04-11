<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kanban Model Classes
    |--------------------------------------------------------------------------
    |
    | The following array allows you to override the default Eloquent model classes
    | used by the Kanban package. This is useful if you need to extend the functionality
    | of the base models or integrate with your own application's models.
    |
    | By default, the package uses its own models:
    | - Board: Pablomadariaga\Kanban\Models\Board
    | - State: Pablomadariaga\Kanban\Models\State
    | - Card: Pablomadariaga\Kanban\Models\Card
    |
    | You may substitute any of these with your own classes (e.g., extend the base class),
    | as long as your class extends the corresponding base model or is compatible with it.
    |
    */

    'models' => [
        'board' => Pablomadariaga\Kanban\Models\Board::class,
        'state' => Pablomadariaga\Kanban\Models\State::class,
        'card'  => Pablomadariaga\Kanban\Models\Card::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Card Title Attribute
    |--------------------------------------------------------------------------
    |
    | When displaying a card on the Kanban board, the package will attempt to show a title
    | or name for the card. By default, it looks for an attribute named "title" on the related
    | model (cardable). You can change this to another attribute name if your model uses a
    | different field for its title (for example, "name" or "subject").
    |
    | If the specified attribute is not found or is empty, the package will fall back to using
    | the model's __toString method (if defined) or a generic "ModelName #ID" format.
    |
    */

    'card_title_attribute' => 'title',

    /*
    |--------------------------------------------------------------------------
    | Assets Fallback
    |--------------------------------------------------------------------------
    |
    | Controls the fallback behavior for loading assets.
    |
    | MAKE SURE TO READ THE DOCS BEFORE MANIPULATING THIS.
    */
    'assets_fallback' => env('KANBAN_ASSETS_FALLBACK', true),

];
