<?php

namespace Tests\Unit\Models;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_fillable_attributes()
    {
        $category = new Category([
            'name' => 'Electronics',
            'user_id' => 1,
            'notes' => 'Test notes',
        ]);

        $this->assertEquals('Electronics', $category->name);
        $this->assertEquals(1, $category->user_id);
        $this->assertEquals('Test notes', $category->notes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Electronics',
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $category->user);
        $this->assertEquals($user->id, $category->user->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_many_appliances()
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Electronics',
            'user_id' => $user->id,
        ]);

        $appliance1 = Appliance::create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'category_id' => $category->id,
            'name' => 'TV',
            'default_wattage' => 100,
        ]);

        $appliance2 = Appliance::create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'category_id' => $category->id,
            'name' => 'Radio',
            'default_wattage' => 50,
        ]);

        $this->assertCount(2, $category->appliances);
        $this->assertTrue($category->appliances->contains($appliance1));
        $this->assertTrue($category->appliances->contains($appliance2));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_created_with_required_fields_only()
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Kitchen',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Kitchen',
            'user_id' => $user->id,
        ]);
        $this->assertNull($category->notes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_created_with_notes()
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Outdoor',
            'user_id' => $user->id,
            'notes' => 'For garden appliances',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Outdoor',
            'user_id' => $user->id,
            'notes' => 'For garden appliances',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_timestamps()
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Test Category',
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($category->created_at);
        $this->assertNotNull($category->updated_at);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function appliances_relationship_returns_empty_collection_when_no_appliances()
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Empty Category',
            'user_id' => $user->id,
        ]);

        $this->assertCount(0, $category->appliances);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $category->appliances);
    }
}
