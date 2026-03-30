<?php
namespace App\Http\Controllers;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Status;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = auth()->user()
            ->conversations()
            ->with([
                'dernierMessage.utilisateur',
                'utilisateurs',
            ])
            ->get()
            ->sortByDesc(function ($conv) {
                return $conv->dernierMessage
                    ? $conv->dernierMessage->created_at
                    : $conv->created_at;
            });

        $statuses = Status::where('created_at', '>=', now()->subDay())
            ->with('user')
            ->latest()
            ->get()
            ->groupBy('user_id');

        $monStatut = Status::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->first();

        return view('chat.index', [
            'conversations'      => $conversations,
            'conversationActive' => null,
            'messages'           => collect(),
            'statuses'           => $statuses,
            'monStatut'          => $monStatut,
        ]);
    }

    public function show(Conversation $conversation)
    {
        $estParticipant = $conversation->utilisateurs()
            ->where('users.id', auth()->id())
            ->exists();

        if (!$estParticipant) {
            abort(403, 'Vous n\'avez pas accès à cette conversation.');
        }

        $conversations = auth()->user()
            ->conversations()
            ->with([
                'dernierMessage.utilisateur',
                'utilisateurs',
            ])
            ->get()
            ->sortByDesc(function ($conv) {
                return $conv->dernierMessage
                    ? $conv->dernierMessage->created_at
                    : $conv->created_at;
            });

        $messages = $conversation->messages()
            ->with('utilisateur')
            ->orderBy('created_at', 'asc')
            ->get();

        $conversation->utilisateurs()->updateExistingPivot(auth()->id(), [
            'last_read_at' => now(),
        ]);

        $statuses = Status::where('created_at', '>=', now()->subDay())
            ->with('user')
            ->latest()
            ->get()
            ->groupBy('user_id');

        $monStatut = Status::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->first();

        return view('chat.index', [
            'conversations'      => $conversations,
            'conversationActive' => $conversation,
            'messages'           => $messages,
            'statuses'           => $statuses,
            'monStatut'          => $monStatut,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $autreUserId = $request->user_id;

        $conversationExistante = auth()->user()
            ->conversations()
            ->where('type', 'private')
            ->whereHas('utilisateurs', function ($query) use ($autreUserId) {
                $query->where('users.id', $autreUserId);
            })
            ->first();

        if ($conversationExistante) {
            return response()->json([
                'conversation_id' => $conversationExistante->id,
            ]);
        }

        $conversation = Conversation::create([
            'type' => 'private',
        ]);

        $conversation->utilisateurs()->attach([
            auth()->id(),
            $autreUserId,
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
        ]);
    }
}