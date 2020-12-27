<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostVideo extends Model
{
    protected $table = 'post_video';
    protected $fillable = ['post_id', 'url'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
}
