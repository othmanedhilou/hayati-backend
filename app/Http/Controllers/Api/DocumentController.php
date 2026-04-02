<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Document::where('user_id', auth()->id());

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $documents = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'documents' => $documents,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'type'            => 'required|in:cin,permis,assurance,passeport,carte_grise,autre',
            'document_number' => 'nullable|string|max:255',
            'issue_date'      => 'nullable|date',
            'expiry_date'     => 'nullable|date|after_or_equal:issue_date',
            'file_path'       => 'nullable|string|max:500',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();

        $document = Document::create($validated);

        return response()->json([
            'message'  => 'Document created successfully.',
            'document' => $document,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $document = Document::where('user_id', auth()->id())->find($id);

        if (! $document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        return response()->json([
            'document' => $document,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $document = Document::where('user_id', auth()->id())->find($id);

        if (! $document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $validated = $request->validate([
            'title'           => 'sometimes|required|string|max:255',
            'type'            => 'sometimes|required|in:cin,permis,assurance,passeport,carte_grise,autre',
            'document_number' => 'nullable|string|max:255',
            'issue_date'      => 'nullable|date',
            'expiry_date'     => 'nullable|date|after_or_equal:issue_date',
            'file_path'       => 'nullable|string|max:500',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $document->update($validated);

        return response()->json([
            'message'  => 'Document updated successfully.',
            'document' => $document,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $document = Document::where('user_id', auth()->id())->find($id);

        if (! $document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully.',
        ]);
    }
}
