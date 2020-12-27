<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'post';
    protected $fillable = ['customer_id', 'description', 'state', 'can_comment', 'is_banner'];

    public function video()
    {
        return $this->hasOne(PostVideo::class);
    }
    public function block()
    {
        return $this->hasMany(PostBlock::class);
    }
    public function comment()
    {
        return $this->hasMany(PostVideo::class);
    }
    public function image()
    {
        return $this->hasMany(PostImage::class);
    }
    public function like()
    {
        return $this->hasMany(PostLike::class);
    }
    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
}
