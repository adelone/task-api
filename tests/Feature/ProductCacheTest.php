<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ProductCacheTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'mysql');

        $this->user = User::factory()->create();

        $this->artisan('migrate');

        $this->artisan('db:seed');
    }

    public function test_first_request_caches_products_and_second_request_uses_cache()
    {
        Product::factory()->count(5)->create();

        $cacheKey = 'products:' . md5(json_encode([]));

        $this->assertNull(Cache::tags(['products'])->get($cacheKey));

        $response1 = $this->actingAs($this->user)->getJson('/api/products');
        $response1->assertOk();

        $this->assertNotNull(Cache::tags(['products'])->get($cacheKey));

        $response2 = $this->actingAs($this->user)->getJson('/api/products');
        $response2->assertOk();

        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_cache_is_invalidated_after_product_creation()
    {
        Product::factory()->count(5)->create();

        $cacheKey = 'products:' . md5(json_encode([]));

        $this->actingAs($this->user)->getJson('/api/products');
        $this->assertNotNull(Cache::tags(['products'])->get($cacheKey));

        $category = Category::factory()->create();

        $this->actingAs($this->user)->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 999,
            'category_id' => $category->id
        ])->assertOk();

        $this->assertNull(Cache::tags(['products'])->get($cacheKey));
    }

    public function test_different_filters_produce_different_cache_keys()
    {
        Product::factory()->count(5)->create();

        $this->actingAs($this->user)->getJson('/api/products?category_id=1');
        $this->actingAs($this->user)->getJson('/api/products');

        $cacheKey1 = 'products:' . md5(json_encode(['category_id' => '1']));
        $cacheKey2 = 'products:' . md5(json_encode([]));

        $this->assertNotNull(Cache::tags(['products'])->get($cacheKey1));
        $this->assertNotNull(Cache::tags(['products'])->get($cacheKey2));

        $this->assertNotEquals(
            Cache::tags(['products'])->get($cacheKey1),
            Cache::tags(['products'])->get($cacheKey2)
        );
    }
}