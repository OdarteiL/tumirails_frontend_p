<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    private RegisterUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RegisterUserAction();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_with_required_fields(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = $this->action->execute($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertEquals('customer', $user->role);
        $this->assertEquals('active', $user->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_with_optional_fields(): void
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'other_names' => 'Marie',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'phone' => '+233500000000',
            'address' => 'Accra, Ghana',
            'role' => 'installer',
        ];

        $user = $this->action->execute($data);

        $this->assertEquals('Marie', $user->other_names);
        $this->assertEquals('+233500000000', $user->phone);
        $this->assertEquals('Accra, Ghana', $user->address);
        $this->assertEquals('installer', $user->role);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_hashes_password(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'plaintextpassword',
        ];

        $user = $this->action->execute($data);

        $this->assertNotEquals('plaintextpassword', $user->password);
        $this->assertTrue(Hash::check('plaintextpassword', $user->password));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_default_role_to_customer(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = $this->action->execute($data);

        $this->assertEquals('customer', $user->role);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_status_to_active(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = $this->action->execute($data);

        $this->assertEquals('active', $user->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_persists_user_to_database(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = $this->action->execute($data);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);
    }
}
