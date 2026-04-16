<?php

namespace App\Http\Controllers;

use App\Models\product_views;
use App\Models\products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductViewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $views = product_views::query()
            ->with('product')
            ->latest()
            ->paginate(20);

        return response()->json($views);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $view = product_views::create($validated);

        return response()->json([
            'message' => 'Product view saved',
            'data' => $view,
        ], 201);
    }

    /**
     * Return globally recommended products based on popularity.
     */
    public function globalRecommendations(Request $request)
    {
        $limit = (int) $request->query('limit', 6);
        $limit = max(1, min($limit, 20));

        $recommended = products::query()
            ->leftJoin('product_views', 'product_views.product_id', '=', 'products.id')
            ->select([
                'products.id',
                'products.title',
                'products.description',
                'products.price',
                'products.discount_price',
                'products.image',
                'products.category_id',
                'products.in_stock',
                'products.created_at',
                'products.updated_at',
                DB::raw('COUNT(product_views.id) as views_count'),
            ])
            ->groupBy([
                'products.id',
                'products.title',
                'products.description',
                'products.price',
                'products.discount_price',
                'products.image',
                'products.category_id',
                'products.in_stock',
                'products.created_at',
                'products.updated_at',
            ])
            ->orderByDesc('views_count')
            ->orderByDesc('products.id')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $recommended,
            'meta' => [
                'count' => $recommended->count(),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Return recommended products based on global view popularity.
     */
    public function recommendations(Request $request, int $productId)
    {
        $limit = (int) $request->query('limit', 6);
        $limit = max(1, min($limit, 20));

        $currentProduct = products::query()->find($productId);
        if (! $currentProduct) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $recommended = products::query()
            ->leftJoin('product_views', 'product_views.product_id', '=', 'products.id')
            ->select([
                'products.id',
                'products.title',
                'products.description',
                'products.price',
                'products.discount_price',
                'products.image',
                'products.category_id',
                'products.in_stock',
                'products.created_at',
                'products.updated_at',
                DB::raw('COUNT(product_views.id) as views_count'),
            ])
            ->where('products.id', '!=', $productId)
            ->groupBy([
                'products.id',
                'products.title',
                'products.description',
                'products.price',
                'products.discount_price',
                'products.image',
                'products.category_id',
                'products.in_stock',
                'products.created_at',
                'products.updated_at',
            ])
            ->orderByDesc('views_count')
            ->orderByDesc('products.id')
            ->limit($limit)
            ->get();

        if ($recommended->isEmpty()) {
            $recommended = products::query()
                ->where('id', '!=', $productId)
                ->latest('id')
                ->limit($limit)
                ->get();
        }

        return response()->json([
            'data' => $recommended,
            'meta' => [
                'source_product_id' => $productId,
                'count' => $recommended->count(),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(product_views $product_views)
    {
        return response()->json($product_views->load('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product_views $product_views)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, product_views $product_views)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product_views $product_views)
    {
        $product_views->delete();

        return response()->json([
            'message' => 'Product view deleted',
        ]);
    }
}
