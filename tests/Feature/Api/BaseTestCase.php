<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected User $userModel;
    protected User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        assert($user instanceof User);
        $this->userModel = $user;

        $another_user = User::factory()->create();
        assert($another_user instanceof User);
        $this->anotherUser = $another_user;

        $this->withHeader('Accept','application/json');
    }

}
