<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function index(): JsonResponse
    {
        $messages = ChatMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role'    => 'required|in:user,assistant',
            'content' => 'required|string|max:5000',
        ]);

        $message = ChatMessage::create([
            'user_id' => auth()->id(),
            'role'    => $validated['role'],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message'      => 'Message saved successfully.',
            'chat_message' => $message,
        ], 201);
    }

    public function ai(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $userId = auth()->id();

        // Save user message
        ChatMessage::create([
            'user_id' => $userId,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Get conversation history (last 20 messages for context)
        $history = ChatMessage::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        $systemPrompt = "Tu es HAYATI, un assistant intelligent marocain. Tu parles en Darija (dialecte marocain), Français, et Arabe selon la préférence de l'utilisateur. Tu aides les utilisateurs avec:

1. **Gestion des papiers**: CIN, permis de conduire, assurance, passeport - rappels d'expiration, conseils sur les démarches administratives au Maroc.
2. **Transport**: Comparer les prix et durées entre taxi, train (ONCF), bus (CTM, Supratours), et tram dans les villes marocaines.
3. **Gestion d'argent**: Conseils de budget, suivi des dépenses, astuces d'économie adaptées au contexte marocain.
4. **Services locaux**: Recommander des plombiers, électriciens, mécaniciens fiables. Donner des estimations de prix au Maroc.
5. **Courses & prix**: Comparer les prix entre BIM, Marjane, Carrefour, Acima. Signaler les promotions.

Tu es amical, pratique, et tu donnes des réponses courtes et utiles. Tu utilises le Dirham (DH) comme monnaie. Tu connais les villes marocaines, les administrations, et la culture locale.

Quand l'utilisateur te parle en Darija, réponds en Darija. Exemple: 'Salam! Kifach n9der n3awnek lyoum?'";

        try {
            $messagesPayload = array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $history
            );

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.api_key'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'max_tokens' => 1024,
                'messages' => $messagesPayload,
            ]);

            if ($response->successful()) {
                $aiContent = $response->json('choices.0.message.content', 'Désolé, je n\'ai pas pu traiter ta demande.');
            } else {
                $aiContent = 'Désolé, le service est temporairement indisponible. Réessaie dans quelques instants.';
            }
        } catch (\Exception $e) {
            $aiContent = 'Désolé, une erreur s\'est produite. Réessaie dans quelques instants.';
        }

        // Save assistant response
        $assistantMessage = ChatMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => $aiContent,
        ]);

        return response()->json([
            'reply' => $aiContent,
            'chat_message' => $assistantMessage,
        ]);
    }
}
