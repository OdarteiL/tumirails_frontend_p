<?php

namespace Tests\Unit\Actions\Appliance;

use App\Actions\Appliance\DeleteApplianceAction;
use App\Models\Appliance;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteApplianceActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteApplianceAction $action;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new DeleteApplianceAction();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function execute_soft_deletes_appliance(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $result = $this->action->execute($appliance);

        $this->assertTrue($result);
        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'is_active' => false,
        ]);
    }

    #[Test]
    public function execute_returns_true_on_success(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $result = $this->action->execute($appliance);

        $this->assertTrue($result);
    }
}
