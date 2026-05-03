<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\CacheService;
use App\Models\Product;

class ProductController extends Controller
{
    public function __construct(
        private CacheService $cache
    ) {}

    public function index(ProductIndexRequest $request)
    {
        $key = $this->cache->makeKey('products', $request->validated());

        return $this->cache
            ->tags(['products'])
            ->ttl(300)
            ->remember($key, function () use ($request) {
                $products = Product::query()
                    ->when($request->filled('category_id'), fn($q) =>
                        $q->whereIn('category_id', explode(',', $request->category_id))
                    )
                    ->when($request->filled('price_min'), fn($q) =>
                        $q->where('price', '>=', $request->price_min)
                    )
                    ->when($request->filled('price_max'), fn($q) =>
                        $q->where('price', '<=', $request->price_max)
                    )
                    ->when($request->filled('name'), fn($q) =>
                        $q->where('name', 'LIKE', '%' . $request->name . '%')
                    )
                    ->when($request->filled('sort_by'), fn($q) =>
                        $q->orderBy($request->sort_by, $request->get('sort_direction', 'desc'))
                    )
                    ->paginate(15);

                return ProductResource::collection($products)->response()->getContent();
            });
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        $this->cache->tags(['products'])->flush();

        return new ProductResource($product->fresh());
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        $this->cache->tags(['products'])->flush();

        return new ProductResource($product->fresh());
    }

    public function destroy(Product $product)
    {
        $product->delete();
        $this->cache->tags(['products'])->flush();

        return response()->noContent();
    }
}