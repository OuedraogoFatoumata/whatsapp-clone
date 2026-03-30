<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
   
    protected $fillable = [
        'user_id',
        'content',
        'media',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
