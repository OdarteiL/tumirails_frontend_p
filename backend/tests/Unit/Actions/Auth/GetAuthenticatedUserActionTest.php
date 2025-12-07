<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\GetAuthenticatedUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAuthenticatedUserActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_the_authenticated_user(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $action = new GetAuthenticatedUserAction();
        $result = $action->execute($user);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals('John', $result->first_name);
        $this->assertEquals('Doe', $result->last_name);
    }

    /** @test */
    public function it_returns_user_with_all_attributes(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'other_names' => 'Marie',
            'email' => 'jane@example.com',
            'phone' => '+233500000000',
            'address' => 'Accra, Ghana',
            'role' => 'provider',
            'status' => 'active',
        ]);

        $action = new GetAuthenticatedUserAction();
        $result = $action->execute($user);

        $this->assertEquals('Marie', $result->other_names);
        $this->assertEquals('+233500000000', $result->phone);
        $this->assertEquals('Accra, Ghana', $result->address);
        $this->assertEquals('provider', $result->role);
        $this->assertEquals('active', $result->status);
    }

    /** @test */
    public function it_returns_same_user_instance(): void
    {
        $user = User::factory()->create();

        $action = new GetAuthenticatedUserAction();
        $result = $action->execute($user);

        $this->assertSame($user, $result);
    }
}
