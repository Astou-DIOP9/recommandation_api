<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\product_views;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search = $request->get('search');
        $category = $request->get('category');
        $sort = $request->get('sort', 'newest');

        $query = Product::query()->with('category')->withCount('views');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        // Apply category filter
        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        // Apply sorting
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'oldest') {
            $query->oldest('id');
        } else {
            $query->latest('id');
        }

        $products = $query->paginate($perPage);

        return response()->json($products);
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
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'image'          => ['nullable', 'string', 'max:10000000'], // Allow large base64 strings
            'in_stock'       => ['nullable', 'boolean'],
            'category'       => ['nullable', 'string'],
            'category_id'    => ['nullable', 'integer'],
        ]);

        // Convert category name to category_id if provided
        if (!empty($validated['category']) && empty($validated['category_id'])) {
            $category = Category::where('name', $validated['category'])->first();
            if ($category) {
                $validated['category_id'] = $category->id;
            }
        }

        // Remove the category field before creating the product
        unset($validated['category']);

        // Convert numeric prices to integers
        $validated['price'] = intval($validated['price'] ?? 0);
        if (!empty($validated['discount_price'])) {
            $validated['discount_price'] = intval($validated['discount_price']);
        }

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created',
            'data' => $product->load('category'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Product $product)
    {
        // Track one view event on each product detail API call.
        product_views::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'session_id' => sha1(($request->ip() ?? 'unknown') . '|' . ($request->userAgent() ?? 'unknown')),
        ]);

        return response()->json($product->load(['category'])->loadCount('views'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'title'          => ['sometimes', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string'],
            'price'          => ['sometimes', 'numeric', 'min:0'],
            'discount_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'image'          => ['sometimes', 'nullable', 'string', 'max:10000000'], // Allow large base64 strings
            'in_stock'       => ['sometimes', 'boolean'],
            'category'       => ['sometimes', 'nullable', 'string'],
            'category_id'    => ['sometimes', 'nullable', 'integer'],
        ]);

        // Convert category name to category_id if provided
        if (!empty($validated['category']) && empty($validated['category_id'])) {
            $category = Category::where('name', $validated['category'])->first();
            if ($category) {
                $validated['category_id'] = $category->id;
            }
        }

        // Remove the category field before updating
        unset($validated['category']);

        // Convert numeric prices to integers if provided
        if (isset($validated['price'])) {
            $validated['price'] = intval($validated['price']);
        }
        if (isset($validated['discount_price'])) {
            $validated['discount_price'] = intval($validated['discount_price']);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated',
            'data' => $product->load('category'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted',
        ]);
    }
}
