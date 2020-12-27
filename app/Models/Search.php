<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    protected $table = 'search';
    protected $fillable = ['customer_id', 'value'];

    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
}
