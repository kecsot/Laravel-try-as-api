<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Requests\DeckCardRequest;
use App\Http\Resources\CardResource;
use App\Models\Card;
use App\Models\Deck;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DeckCardController extends Controller
{

    public function index(Request $request, $id): CardResource
    {
        $deck = Deck::findOrFail($id);
        if ($deck->owner->id != $request->user()->id) {
            throw new AccessDeniedHttpException();
        }

        return new CardResource($deck->cards);
    }

    public function store(DeckCardRequest $request)
    {
        try {
            $card = new Card();
            $card->owner_id = $request->user()->id;
            $card->fill($request->validated());
            $card->save();

            return new CardResource($card);
        } catch (Exception $exception) {
            throw new BadRequestException();
        }
    }

    public function show(Request $request, $id): CardResource
    {
        $card = Card::findOrFail($id);
        if ($card->owner->id != $request->user()->id) {
            throw new AccessDeniedHttpException();
        }

        return new CardResource($card);
    }

    public function update(DeckCardRequest $request, $id)
    {
        if (!$id) throw new BadRequestException("Invalid id");

        $card = Card::findOrFail($id);
        if ($card->owner->id != $request->user()->id) {
            throw new AccessDeniedHttpException();
        }

        try {
            $card->fill($request->validated())->save();
            return new CardResource($card);
        } catch (Exception $exception) {
            throw new BadRequestException();
        }
    }

    public function destroy(Request $request, $id)
    {
        $card = Card::findOrfail($id);
        if ($card->owner->id != $request->user()->id) {
            throw new AccessDeniedHttpException();
        }
        $card->delete();

        return response()->json(null, 204);
    }

}
