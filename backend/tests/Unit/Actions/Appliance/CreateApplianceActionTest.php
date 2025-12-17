<?php

namespace Tests\Unit\Actions\Appliance;

use App\Actions\Appliance\CreateApplianceAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateApplianceActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateApplianceAction $action;

    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new CreateApplianceAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function execute_creates_appliance_with_required_fields(): void
    {
        $data = [
            'name' => 'Test Appliance',
            'category_id' => $this->category->id,
            'default_wattage' => 100,
        ];

        $appliance = $this->action->execute($this->user->id, User::class, $data, false);

        $this->assertInstanceOf(Appliance::class, $appliance);
        $this->assertEquals('Test Appliance', $appliance->name);
        $this->assertEquals($this->user->id, $appliance->owner_id);
        $this->assertFalse($appliance->is_public);
        $this->assertTrue($appliance->is_active);
    }

    #[Test]
    public function execute_creates_appliance_with_optional_fields(): void
    {
        $data = [
            'name' => 'Advanced Appliance',
            'category_id' => $this->category->id,
            'default_wattage' => 200,
            'default_usage_hours' => 8,
            'metadata' => ['efficiency_rating' => 'A+'],
        ];

        $appliance = $this->action->execute($this->user->id, User::class, $data, true);

        $this->assertEquals(8, $appliance->default_usage_hours);
        $this->assertEquals(['efficiency_rating' => 'A+'], $appliance->metadata);
        $this->assertTrue($appliance->is_public);
    }
}
