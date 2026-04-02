<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function index(): JsonResponse
    {
        $budgets = Budget::where('user_id', auth()->id())
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'budgets' => $budgets,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month'         => 'required|string|size:7', // YYYY-MM
            'income_target' => 'required|numeric|min:0',
            'expense_limit' => 'required|numeric|min:0',
        ]);

        $budget = Budget::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'month'   => $validated['month'],
            ],
            [
                'income_target' => $validated['income_target'],
                'expense_limit' => $validated['expense_limit'],
            ]
        );

        $statusCode = $budget->wasRecentlyCreated ? 201 : 200;

        return response()->json([
            'message' => $budget->wasRecentlyCreated
                ? 'Budget created successfully.'
                : 'Budget updated successfully.',
            'budget'  => $budget,
        ], $statusCode);
    }

    public function show(int $id): JsonResponse
    {
        $budget = Budget::where('user_id', auth()->id())->find($id);

        if (! $budget) {
            return response()->json(['message' => 'Budget not found.'], 404);
        }

        // Get actual income/expense for this budget's month
        $actuals = Transaction::where('user_id', auth()->id())
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$budget->month])
            ->select(
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('type')
            ->pluck('total', 'type');

        return response()->json([
            'budget'         => $budget,
            'actual_income'  => (float) ($actuals['income'] ?? 0),
            'actual_expense' => (float) ($actuals['expense'] ?? 0),
        ]);
    }
}
