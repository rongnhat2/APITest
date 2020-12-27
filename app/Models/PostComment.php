<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $table = 'post_comment';
    protected $fillable = ['post_id', 'customer_id', 'comment'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
}
