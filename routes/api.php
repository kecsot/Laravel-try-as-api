<?php

use App\Http\Controllers\ApiController\AuthController;
use App\Http\Controllers\ApiController\CardController;
use App\Http\Controllers\ApiController\DeckCardController;
use App\Http\Controllers\ApiController\DeckController;
use App\Http\Resources\DeckResource;
use App\Models\Deck;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/token', [AuthController::class, 'token']);

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('decks', DeckController::class)
        ->except(['create','edit']);

    Route::resource('decks.cards', DeckCardController::class)
        ->shallow()
        ->except(['create','edit']);
});
