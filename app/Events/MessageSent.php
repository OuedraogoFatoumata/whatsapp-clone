<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    
    public Message $message;

    
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    
    public function broadcastOn(): array
    {
        return [
           
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id'              => $this->message->id,
                'body'            => $this->message->body,
                'type'            => $this->message->type,
                'user_id'         => $this->message->user_id,
                'conversation_id' => $this->message->conversation_id,
                'auteur'          => $this->message->utilisateur->name,
                'avatar'          => $this->message->utilisateur->avatar,
                'created_at'      => $this->message->created_at,
            ],
        ];
    }

    
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}