<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsfeed extends Model
{
    /**
     * The database table newsfeed by the model.
     *
     * @var string
     */
    protected $table = 'customer_newsfeed';
    protected $fillable = ['customer_id', 'prev_load_id', 'prev_id', 'prev_index_id', 'index_id', 'next_id', 'next_load_id'];

}
