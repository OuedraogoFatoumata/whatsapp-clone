<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',          
        'username',      
        'email',         
        'password',      
        'avatar',        
        'is_online',     
        'last_seen_at', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at'      => 'datetime',  
            'is_online'         => 'boolean',
        ];
    }
     
    public function conversations()
{
    return $this->belongsToMany(Conversation::class, 'conversation_user', 'user_id', 'conversation_id')
                ->withPivot('last_read_at')
                ->withTimestamps();
}
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}

