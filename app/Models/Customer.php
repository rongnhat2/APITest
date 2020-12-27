<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    /**
     * The database table role by the model.
     *
     * @var string
     */
    protected $table = 'customer';
    protected $fillable = ['secret_key', 'token'];

    public function customer_detail()
    {
        return $this->hasOne(CustomerDetail::class);
    }
    public function post_block()
    {
        return $this->hasMany(PostBlock::class);
    }
    public function post_like()
    {
        return $this->hasMany(PostLike::class);
    }
    public function block_comment()
    {
        return $this->post_block(PostComment::class);
    }
}
