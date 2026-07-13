<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 注册功能测试 — 仅在 ALLOW_REGISTRATION=true 时运行。
     * 生产环境默认关闭注册，测试环境通过 phpunit.xml 设置为 true。
     */

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! config('app.allow_registration', false)) {
            $this->markTestSkipped('公开注册未启用（ALLOW_REGISTRATION=false）');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        if (! config('app.allow_registration', false)) {
            $this->markTestSkipped('公开注册未启用（ALLOW_REGISTRATION=false）');
        }

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
