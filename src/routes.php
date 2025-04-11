<?php

use Illuminate\Support\Facades\Route;
use TallStackUi\Foundation\Http\Controllers\KanbanAssetsController;

Route::name('kaban.')
    ->prefix('/kaban')
    ->group(function () {
        Route::get('/script/{file?}', [KanbanAssetsController::class, 'script'])->name('script');
    });
