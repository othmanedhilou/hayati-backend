<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderReview;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ServiceCategory::all();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function providers(Request $request, int $categoryId): JsonResponse
    {
        $category = ServiceCategory::find($categoryId);

        if (! $category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $query = ServiceProvider::where('category_id', $categoryId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $sortBy = $request->input('sort', 'avg_rating');
        $sortDir = $request->input('dir', 'desc');

        if (in_array($sortBy, ['avg_rating', 'name', 'price_min'])) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $providers = $query->get();

        return response()->json([
            'category'  => $category,
            'providers' => $providers,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $provider = ServiceProvider::with(['category', 'reviews.user'])->find($id);

        if (! $provider) {
            return response()->json(['message' => 'Provider not found.'], 404);
        }

        return response()->json([
            'provider' => $provider,
        ]);
    }

    public function storeReview(Request $request, int $providerId): JsonResponse
    {
        $provider = ServiceProvider::find($providerId);

        if (! $provider) {
            return response()->json(['message' => 'Provider not found.'], 404);
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = ProviderReview::create([
            'user_id'     => auth()->id(),
            'provider_id' => $providerId,
            'rating'      => $validated['rating'],
            'comment'     => $validated['comment'] ?? null,
        ]);

        // Update provider avg_rating and total_reviews
        $provider->total_reviews = $provider->reviews()->count();
        $provider->avg_rating = $provider->reviews()->avg('rating');
        $provider->save();

        return response()->json([
            'message' => 'Review added successfully.',
            'review'  => $review,
        ], 201);
    }
}
