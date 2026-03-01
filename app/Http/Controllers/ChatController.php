<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
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

        return view('chat.index', [
            'conversations'        => $conversations,
            'conversationActive'   => null,
            'messages'             => collect(),
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

        return view('chat.index', [
            'conversations'      => $conversations,
            'conversationActive' => $conversation,
            'messages'           => $messages,
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