<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransportRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function index(): JsonResponse
    {
        $routes = TransportRoute::with('options')->get();

        return response()->json([
            'routes' => $routes,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'origin'      => 'required|string|max:255',
            'destination' => 'required|string|max:255',
        ]);

        $routes = TransportRoute::with('options')
            ->where('origin', 'like', '%' . $request->origin . '%')
            ->where('destination', 'like', '%' . $request->destination . '%')
            ->get();

        return response()->json([
            'routes' => $routes,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $route = TransportRoute::with('options')->find($id);

        if (! $route) {
            return response()->json(['message' => 'Route not found.'], 404);
        }

        return response()->json([
            'route' => $route,
        ]);
    }
}
