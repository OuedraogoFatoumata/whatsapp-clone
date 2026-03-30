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
                    'id'        => $message->id,
                    'body'      => $message->body,
                    'type'      => $message->type,
                    'user_id'   => $message->user_id,
                    'est_moi'   => $message->estDeMonCote(),
                    'auteur'    => $message->utilisateur->name,
                    'file_path' => $message->file_path ? asset('storage/' . $message->file_path) : null,
                    'file_type' => $message->file_type,
                    'created_at' => $message->created_at,
                ];
            });

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        // Validation — body obligatoire seulement si pas de fichier
        $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
            'body'            => ['nullable', 'string', 'max:5000'],
            'fichier'         => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv'],
        ], [
            'fichier.max'   => 'Le fichier ne peut pas dépasser 20 Mo.',
            'fichier.mimes' => 'Format non supporté. Utilisez jpg, png, gif, mp4, mov...',
        ]);

        // Il faut au moins un message ou un fichier
        if (!$request->body && !$request->hasFile('fichier')) {
            return response()->json(['error' => 'Envoyez un message ou un fichier.'], 422);
        }

        $conversation = Conversation::findOrFail($request->conversation_id);

        $estParticipant = $conversation->utilisateurs()
            ->where('users.id', auth()->id())
            ->exists();

        if (!$estParticipant) {
            abort(403, 'Vous ne pouvez pas envoyer de message dans cette conversation.');
        }

        // Upload du fichier
        $filePath = null;
        $fileType = null;

        if ($request->hasFile('fichier')) {
            $file     = $request->file('fichier');
            $filePath = $file->store('messages', 'public');
            $mime     = $file->getMimeType();

            // Déterminer le type
            if (str_starts_with($mime, 'image/')) {
                $fileType = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $fileType = 'video';
            } else {
                $fileType = 'file';
            }
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => auth()->id(),
            'body'            => $request->body ?? '',
            'type'            => 'text',
            'file_path'       => $filePath,
            'file_type'       => $fileType,
        ]);

        $message->load('utilisateur');

        $messageFormate = [
            'id'        => $message->id,
            'body'      => $message->body,
            'type'      => $message->type,
            'user_id'   => $message->user_id,
            'est_moi'   => true,
            'auteur'    => $message->utilisateur->name,
            'avatar'    => $message->utilisateur->avatar,
            'file_path' => $message->file_path ? asset('storage/' . $message->file_path) : null,
            'file_type' => $message->file_type,
            'created_at' => $message->created_at,
        ];

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['message' => $messageFormate], 201);
    }
}