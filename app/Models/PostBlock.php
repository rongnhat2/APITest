<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostBlock extends Model
{
    protected $table = 'post_block';
    protected $fillable = ['post_id', 'customer_id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
}
