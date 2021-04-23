<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
    }

    public function authenticated(string $guard = 'sanctum'): User
    {
        $user = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Beeproger',
        ]);

        $this->actingAs($user, $guard);

        return $user;
    }
}
