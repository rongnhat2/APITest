<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBlock extends Model
{
    protected $table = 'customer_block';
    protected $fillable = ['customer_id_01', 'customer_id_02'];

    public function customer_01()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id_01', 'customer_id');
    }
    public function customer_02()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id_02', 'customer_id');
    }
}
