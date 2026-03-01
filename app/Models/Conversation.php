<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;

    
    protected $table = 'conversations';

    protected $fillable = [
        'type', 
        'name', 
    ];

   
    public function utilisateurs()
    {
        return $this->belongsToMany(
            User::class,
            'conversation_user',  
            'conversation_id',    
            'user_id'          
        )
        ->withPivot('last_read_at')
        ->withTimestamps();
    }

  
    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

   
    public function dernierMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

   
    public function autreUtilisateur()
    {
        return $this->utilisateurs()
                    ->where('users.id', '!=', auth()->id())
                    ->first();
    }
}