<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::where('user_id', auth()->id());

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'transactions' => $transactions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'        => 'required|in:income,expense',
            'amount'      => 'required|numeric|min:0.01',
            'category'    => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'date'        => 'required|date',
        ]);

        $validated['user_id'] = auth()->id();

        $transaction = Transaction::create($validated);

        return response()->json([
            'message'     => 'Transaction created successfully.',
            'transaction' => $transaction,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $transaction = Transaction::where('user_id', auth()->id())->find($id);

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        $validated = $request->validate([
            'type'        => 'sometimes|required|in:income,expense',
            'amount'      => 'sometimes|required|numeric|min:0.01',
            'category'    => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:500',
            'date'        => 'sometimes|required|date',
        ]);

        $transaction->update($validated);

        return response()->json([
            'message'     => 'Transaction updated successfully.',
            'transaction' => $transaction,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $transaction = Transaction::where('user_id', auth()->id())->find($id);

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully.',
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|string|size:7', // format: YYYY-MM
        ]);

        $month = $request->month;

        $totals = Transaction::where('user_id', auth()->id())
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month])
            ->select(
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('type')
            ->pluck('total', 'type');

        return response()->json([
            'month'         => $month,
            'total_income'  => (float) ($totals['income'] ?? 0),
            'total_expense' => (float) ($totals['expense'] ?? 0),
            'balance'       => (float) (($totals['income'] ?? 0) - ($totals['expense'] ?? 0)),
        ]);
    }
}
