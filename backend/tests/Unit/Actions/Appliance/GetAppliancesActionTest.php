<?php

namespace Tests\Unit\Actions\Appliance;

use App\Actions\Appliance\GetAppliancesAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetAppliancesActionTest extends TestCase
{
    use RefreshDatabase;

    private GetAppliancesAction $action;

    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new GetAppliancesAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function execute_returns_visible_appliances(): void
    {
        Appliance::factory()->public()->count(3)->create(['category_id' => $this->category->id]);
        Appliance::factory()->private()->count(2)->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        $result = $this->action->execute($this->user->id, User::class);

        $this->assertEquals(5, $result->total());
    }

    #[Test]
    public function execute_filters_by_category(): void
    {
        $category2 = Category::factory()->create();

        Appliance::factory()->public()->count(2)->create(['category_id' => $this->category->id]);
        Appliance::factory()->public()->count(3)->create(['category_id' => $category2->id]);

        $result = $this->action->execute($this->user->id, User::class, $this->category->id);

        $this->assertEquals(2, $result->total());
    }

    #[Test]
    public function execute_filters_by_search(): void
    {
        Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
            'name' => 'LED Bulb',
        ]);
        Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
            'name' => 'Fan',
        ]);

        $result = $this->action->execute($this->user->id, User::class, null, 'LED');

        $this->assertEquals(1, $result->total());
    }

    #[Test]
    public function execute_paginates_results(): void
    {
        Appliance::factory()->public()->count(20)->create(['category_id' => $this->category->id]);

        $result = $this->action->execute($this->user->id, User::class, null, null, 10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(20, $result->total());
    }
}
