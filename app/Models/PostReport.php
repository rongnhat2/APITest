<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{
    protected $table = 'post_report';
    protected $fillable = ['post_id', 'customer_id', 'report_value'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
}
