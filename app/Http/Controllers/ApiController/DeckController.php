<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Requests\DeckRequest;
use App\Http\Resources\DeckResource;
use App\Models\Deck;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DeckController extends Controller
{

    public function index(Request $request)
    {
        return DeckResource::collection($request->user()->decks);
    }

    public function store(DeckRequest $request)
    {
        try {
            $deck = new Deck();
            $deck->owner_id = $request->user()->id;
            $deck->fill($request->validated());
            $deck->save();

            return new DeckResource($deck);
        } catch (Exception $exception) {
            throw new BadRequestException();
        }
    }

    public function show(Request $request, $id): DeckResource
    {
        $deck = Deck::findOrFail($id);

        if ($deck->owner->id != $request->user()->id) {
            throw new AccessDeniedHttpException();
        }

        return new DeckResource($deck);
    }


    public function update(DeckRequest $request, $id)
    {
        if (!$id) throw new BadRequestException("Invalid id");

        $deck = Deck::findOrFail($id);
        if ($deck->owner->id != $request->user()->id) {
            throw new AccessDeniedHttpException();
        }

        try {
            $deck->fill($request->validated())->save();

            return new DeckResource($deck);
        } catch (Exception $exception) {
            throw new BadRequestException();
        }
    }

    public function destroy(Request $request, $id)
    {
        $deck = Deck::findOrfail($id);
        if ($deck->owner->id != $request->user()->id) {
            throw new AccessDeniedHttpException();
        }
        $deck->delete();

        return response()->json(null, 204);
    }
}
