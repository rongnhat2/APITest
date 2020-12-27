<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting';
    protected $fillable = ['customer_id', 'like_comment', 'from_friends', 'requested_friend', 'suggested_friend', 'birthday', 'video', 'report', 'sound_on', 'notification_on', 'vibrant_on', 'led_on'];

    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
}
