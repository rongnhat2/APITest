<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    protected $table = 'post_image';
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
