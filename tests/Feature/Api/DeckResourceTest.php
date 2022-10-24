<?php

namespace Tests\Feature\Api;

use App\Models\Deck;
use Faker\Factory;
use Illuminate\Testing\Fluent\AssertableJson;

class DeckResourceTest extends BaseTestCase
{
    private Deck $deckOwnedUserModel;

    public function test_index_auth()
    {
        $response = $this->get("/api/decks");
        $response->assertUnauthorized();

        $response = $this->actingAs($this->userModel)->get("/api/decks");
        $response->assertOk();
    }

    public function test_index_item_found()
    {
        $response = $this->actingAs($this->userModel)->get("/api/decks");
        $response->assertJsonFragment(['id' => $this->deckOwnedUserModel->id]);

        $response = $this->actingAs($this->anotherUser)->get("/api/decks");
        $response->assertJsonMissing(['id' => $this->deckOwnedUserModel->id]);

        $deck = Deck::factory()->create();
        assert($deck instanceof Deck);
        $response = $this->actingAs($this->userModel)->get("/api/decks");
        $response->assertJsonMissing(['id' => $deck->id]);
    }

    public function test_index_all_found()
    {
        $response = $this->actingAs($this->userModel)->get("/api/decks");
        $count_of_user_decks = count($this->userModel->decks);
        $response->assertJsonCount($count_of_user_decks, 'data');
    }

    public function test_show_auth()
    {
        $response = $this->get("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertUnauthorized();

        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertOk();
    }

    public function test_show_missing()
    {
        $response = $this->actingAs($this->userModel)->get("/api/decks/-1");
        $response->assertNotFound();
    }

    public function test_show_owner()
    {
        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertOk();

        $response = $this->actingAs($this->anotherUser)->get("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertForbidden();
    }

    public function test_show_values()
    {
        $deck = $this->deckOwnedUserModel;
        $response = $this->actingAs($this->userModel)
            ->get("/api/decks/{$deck->id}");

        $response->assertJson(fn(AssertableJson $json) => $json
            ->hasAll([
                'data.id',
                'data.name',
                'data.description',
                'data.owner_id',
                'data.created_at',
                'data.updated_at',
            ])
        );
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('data.id', $deck->id)
            ->where('data.name', $deck->name)
            ->where('data.description', $deck->description)
            ->where('data.owner_id', $this->userModel->id)
        );
    }

    public function test_create_not_impl()
    {
        $response = $this->actingAs($this->userModel)->get("/api/decks/create");
        $response->assertNotFound();
    }

    public function test_store_auth()
    {
        $response = $this->post("/api/decks", []);
        $response->assertUnauthorized();

        $response = $this->actingAs($this->userModel)->post("/api/decks", []);
        $response->assertUnprocessable();
    }

    public function test_store_and_check_response()
    {
        $data = $this->getRandomDeckDataArray();

        $response = $this->actingAs($this->userModel)->post("/api/decks", $data);

        $response->assertCreated();
        $response->assertJsonFragment($data);
    }

    private function getRandomDeckDataArray(): array
    {
        $faker = Factory::create();
        return [
            'name' => $faker->name,
            'description' => $faker->realTextBetween(50, 100)
        ];
    }

    public function test_edit_not_impl()
    {
        $response = $this->actingAs($this->userModel)->get("/api/decks/{$this->deckOwnedUserModel->id}/edit");
        $response->assertNotFound();
    }

    public function test_update_auth()
    {
        $data = $this->getRandomDeckDataArray();

        // Unauth
        $response = $this->patch("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertUnauthorized();

        // Not own
        $response = $this->actingAs($this->anotherUser)->patch("/api/decks/{$this->deckOwnedUserModel->id}", $data);
        $response->assertForbidden();

        // Own
        $response = $this->actingAs($this->userModel)->patch("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertUnprocessable();
    }

    public function test_update_and_check_response()
    {
        $data = $this->getRandomDeckDataArray();
        // Post to prepare to change
        $response = $this->actingAs($this->userModel)->post("/api/decks", $data);
        $response->assertCreated();
        $data = $response->json("data");
        $deck_id = $data['id'];

        // Change
        $response = $this->actingAs($this->userModel)->patch("/api/decks/{$deck_id}", $data);
        $response->assertJsonFragment($data);
    }

    public function test_delete_auth()
    {
        // Unauth
        $response = $this->delete("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertUnauthorized();

        // Not own
        $response = $this->actingAs($this->anotherUser)->delete("/api/decks/{$this->deckOwnedUserModel->id}");
        $response->assertForbidden();

        // Own
        $response = $this->actingAs($this->userModel)->delete("/api/decks/{$this->deckOwnedUserModel->id}");
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
