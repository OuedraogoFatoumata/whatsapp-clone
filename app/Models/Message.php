<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'type',
        'file_path',
        'file_type',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estDeMonCote(): bool
    {
        return $this->user_id === auth()->id();
    }
}