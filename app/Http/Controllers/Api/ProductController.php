<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ProductCategory::all();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function products(int $categoryId): JsonResponse
    {
        $category = ProductCategory::find($categoryId);

        if (! $category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $products = Product::where('category_id', $categoryId)->get();

        return response()->json([
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function compare(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $prices = ProductPrice::with('store')
            ->where('product_id', $productId)
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'product' => $product,
            'prices'  => $prices,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|max:255',
        ]);

        $products = Product::where('name', 'like', '%' . $request->q . '%')
            ->orWhere('name_ar', 'like', '%' . $request->q . '%')
            ->with('category')
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function promotions(): JsonResponse
    {
        $promotions = Promotion::with(['product', 'store'])
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->orderBy('discount_percent', 'desc')
            ->get();

        return response()->json([
            'promotions' => $promotions,
        ]);
    }
}
