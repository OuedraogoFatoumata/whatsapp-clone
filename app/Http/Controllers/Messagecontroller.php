<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class MessageController extends Controller
{
   
    public function index(Conversation $conversation)
    {
        
        $estParticipant = $conversation->utilisateurs()
            ->where('users.id', auth()->id())
            ->exists();

        if (!$estParticipant) {
            abort(403, 'Accès refusé.');
        }

        
        $messages = $conversation->messages()
            ->with('utilisateur')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id'         => $message->id,
                    'body'       => $message->body,
                    'type'       => $message->type,
                    'user_id'    => $message->user_id,
                    'est_moi'    => $message->estDeMonCote(),
                    'auteur'     => $message->utilisateur->name,
                    'created_at' => $message->created_at,
                ];
            });

        return response()->json($messages);
    }

   
    public function store(Request $request)
    {
        
        $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
            'body'            => ['required', 'string', 'max:5000'],
        ], [
            'conversation_id.required' => 'La conversation est obligatoire.',
            'conversation_id.exists'   => 'Cette conversation n\'existe pas.',
            'body.required'            => 'Le message ne peut pas être vide.',
            'body.max'                 => 'Le message ne peut pas dépasser 5000 caractères.',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

       
        $estParticipant = $conversation->utilisateurs()
            ->where('users.id', auth()->id())
            ->exists();

        if (!$estParticipant) {
            abort(403, 'Vous ne pouvez pas envoyer de message dans cette conversation.');
        }

       
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => auth()->id(),
            'body'            => $request->body,
            'type'            => 'text',
        ]);

        $message->load('utilisateur');

       
        $messageFormate = [
            'id'         => $message->id,
            'body'       => $message->body,
            'type'       => $message->type,
            'user_id'    => $message->user_id,
            'est_moi'    => true,
            'auteur'     => $message->utilisateur->name,
            'avatar'     => $message->utilisateur->avatar,
            'created_at' => $message->created_at,
        ];

        
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $messageFormate,
        ], 201);
    }
}