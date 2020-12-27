<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'conversation';
    protected $fillable = ['conversation_code', 'customer_id_01', 'customer_id_02'];

    public function customer_01()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id_01', 'customer_id');
    }
    public function customer_02()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id_02', 'customer_id');
    }
    public function message()
    {
        return $this->hasMany(Message::class, 'conversation_code', 'conversation_code');
    }
}
