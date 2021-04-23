<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ForgotPasswordNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_login_a_user(): void
    {
        $this->useSession();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postLogin(['email' => 'test@example.com', 'password' => 'password'])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
            ]);

        $this->assertAuthenticatedAs($user, 'sanctum');
    }

    /** @test */
    public function it_fails_when_an_employee_logs_in_with_invalid_credentials(): void
    {
        $this->useSession();
        $this->withExceptionHandling();

        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postLogin(['email' => 'test@example.com', 'password' => 'wrong'])
            ->assertStatus(422);
    }

    /** @test */
    public function it_can_log_a_user_out(): void
    {
        $this->useSession();
        $this->authenticated('web');

        $this->postJson(route('logout'))
            ->assertStatus(204);

        self::assertFalse($this->isAuthenticated(), 'The user is authenticated');
    }

    /** @test */
    public function it_can_send_a_forgot_password_mail(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson(route('password.email'), ['email' => $user->email])
            ->assertStatus(200);

        Notification::assertSentTo([$user], ForgotPasswordNotification::class, function ($notification) use ($user) {
            return Hash::check($notification->token, DB::table('password_resets')->where('email', $user->email)->latest()->first()->token);
        });
    }

    /** @test */
    public function it_can_reset_a_user_password(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => 'old_password',
        ]);

        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
        ]);

        $this->postJson(route('password.update'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ])
            ->assertStatus(200);

        $this->assertTrue(Hash::check('new_password', $user->refresh()->password));

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    /** @test */
    public function it_can_get_the_current_user(): void
    {
        $user = $this->authenticated();

        $this->getJson(route('me'))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
            ]);
    }

    protected function postLogin(array $request): TestResponse
    {
        return $this->postJson('api/login', $request);
    }

    protected function useSession(): void
    {
        $this->app[Kernel::class]->pushMiddleware(StartSession::class);
    }
}
