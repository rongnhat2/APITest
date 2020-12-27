<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'message';
    protected $fillable = ['conversation_code', 'customer_id', 'message', 'status'];

    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
    public function conversation()
    {
        return $this->hasMany(Conversation::class, 'conversation_code', 'conversation_code');
    }
}
