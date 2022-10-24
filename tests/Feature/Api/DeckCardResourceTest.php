<?php

namespace Tests\Feature\Api;

use App\Models\Card;
use App\Models\Deck;
use Faker\Factory;
use Illuminate\Testing\Fluent\AssertableJson;

class DeckCardResourceTest extends BaseTestCase
{
    private Deck $deckOwnedUserModel;

    public function test_index_auth()
    {
        $response = $this->get("/api/decks/{$this->deckOwnedUserModel->id}/cards");
        $response->assertUnauthorized();

        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}/cards");
        $response->assertOk();
    }

    public function test_index_count()
    {
        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}/cards");
        $response->assertJsonCount(0, 'data');

        $items = $this->generateRandomCardsForModel($this->deckOwnedUserModel);
        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}/cards");
        $response->assertJsonCount(count($items), 'data');
    }

    private function generateRandomCardsForModel(Deck $deck, $max = 10): array
    {
        $random_count = rand(1, $max);
        $items = [];
        for ($i = 0; $i < $random_count; $i++) {
            $items[] = Card::factory()->create(['deck_id' => $deck->id]);
        }
        return $items;
    }

    public function test_index_item_found()
    {
        $card = Card::factory()->create([
            'owner_id' => $this->userModel->id,
            'deck_id' => $this->deckOwnedUserModel->id
        ]);

        // Found
        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}/cards");
        $response->assertJsonFragment(['id' => $card->id]);

        $response = $this->actingAs($this->anotherUser)->get("/api/decks/{$this->deckOwnedUserModel->id}/cards");
        $response->assertJsonMissing(['id' => $card->id]);

        // Not found
        $random_card = Card::factory()->create();
        assert($random_card instanceof Card);
        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}/cards");
        $response->assertJsonMissing(['id' => $random_card->id]);
    }

    public function test_index_values()
    {
        $deck = $this->deckOwnedUserModel;
        $items = $this->generateRandomCardsForModel($this->deckOwnedUserModel, 1);
        $card = reset($items);
        assert($card instanceof Card);

        $response = $this->actingAs($this->userModel)->get("/api/decks/{$deck->id}/cards");

        $response->assertJson(fn(AssertableJson $json) => $json
            ->hasAll([
                'data.0.id',
                'data.0.name'
            ])
        );
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('data.0.id', $card->id)
            ->where('data.0.name', $card->name)
        );
    }

    public function test_show_auth()
    {
        $card = Card::factory()->create(['owner_id' => $this->userModel->id]);

        $response = $this->get("/api/cards/{$card->id}");
        $response->assertUnauthorized();

        $response = $this->actingAs($this->userModel)->get("/api/cards/{$card->id}");
        $response->assertOk();
    }

    public function test_show_missing()
    {
        $response = $this->actingAs($this->userModel)->get("/api/cards/-1");
        $response->assertNotFound();
    }

    public function test_show_owner()
    {
        $card = Card::factory()->create(['owner_id' => $this->userModel->id]);

        $response = $this->actingAs($this->userModel)->get("/api/cards/{$card->id}");
        $response->assertOk();

        $response = $this->actingAs($this->anotherUser)->get("/api/cards/{$card->id}");
        $response->assertForbidden();
    }

    public function test_show_values()
    {
        $card = Card::factory()->create(['owner_id' => $this->userModel->id]);
        $response = $this->actingAs($this->userModel)->get("/api/cards/{$card->id}");

        $response->assertJson(fn(AssertableJson $json) => $json
            ->hasAll([
                'data.id',
                'data.name',
                'data.deck_id',
                'data.owner_id',
                'data.created_at',
                'data.updated_at',
            ])
        );
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('data.id', $card->id)
            ->where('data.name', $card->name)
            ->where('data.deck_id', $card->deck_id)
            ->where('data.owner_id', $this->userModel->id)
        );
    }

    public function test_create_not_impl()
    {
        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}/decks/create");
        $response->assertNotFound();
    }

    public function test_store_auth()
    {
        $card = Card::factory()->create(['owner_id' => $this->userModel->id]);

        $response = $this->post("/api/decks/{$card->id}/cards", []);
        $response->assertUnauthorized();

        $response = $this->actingAs($this->userModel)->post("/api/decks/{$card->id}/cards/", []);
        $response->assertUnprocessable();
    }

    public function test_store_and_check_response()
    {
        $data = $this->getRandomCardDataArray($this->deckOwnedUserModel);
        $response = $this->actingAs($this->userModel)
            ->post("/api/decks/{$this->deckOwnedUserModel->id}/cards", $data);

        $response->assertCreated();
        $response->assertJsonFragment($data);
    }

    private function getRandomCardDataArray(Deck $deck): array
    {
        $faker = Factory::create();
        return [
            'name' => $faker->name,
            'deck_id' => $deck->id
        ];
    }

    public function test_edit_not_impl()
    {
        $response = $this->actingAs($this->userModel)->get("/api/cards/{$this->deckOwnedUserModel->id}/edit");
        $response->assertNotFound();
    }

    public function test_update_auth()
    {
        $data = $this->getRandomCardDataArray($this->deckOwnedUserModel);
        $card = Card::factory()->create(['owner_id' => $this->userModel->id]);

        // Unauth
        $response = $this->patch("/api/cards/{$card->id}");
        $response->assertUnauthorized();

        // Not own
        $response = $this->actingAs($this->anotherUser)->patch("/api/cards/{$card->id}", $data);
        $response->assertForbidden();

        // Own
        $response = $this->actingAs($this->userModel)->patch("/api/cards/{$card->id}");
        $response->assertUnprocessable();
    }

    public function test_update_and_check_response()
    {
        $data = $this->getRandomCardDataArray($this->deckOwnedUserModel);
        // Post to prepare to change
        $response = $this->actingAs($this->userModel)->post("/api/decks/{$this->deckOwnedUserModel->id}/cards", $data);
        $response->assertCreated();
        $data = $response->json("data");
        $deck_id = $data['id'];

        // Change
        $response = $this->actingAs($this->userModel)->patch("/api/cards/{$deck_id}", $data);
        $response->assertJsonFragment($data);
    }

    public function test_delete_auth()
    {
        $card = Card::factory()->create(['owner_id' => $this->userModel->id]);

        // Unauth
        $response = $this->delete("/api/cards/{$card->id}");
        $response->assertUnauthorized();

        // Not own
        $response = $this->actingAs($this->anotherUser)->delete("/api/cards/{$card->id}");
        $response->assertForbidden();

        // Own
        $response = $this->actingAs($this->userModel)->delete("/api/cards/{$card->id}");
        $response->assertNoContent();
    }

    public function test_delete()
    {
        $response = $this->actingAs($this->userModel)->delete("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertNoContent();

        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertNotFound();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $deck_owned = Deck::factory()->create(['owner_id' => $this->userModel->id]);
        assert($deck_owned instanceof Deck);
        $this->deckOwnedUserModel = $deck_owned;
    }

}
