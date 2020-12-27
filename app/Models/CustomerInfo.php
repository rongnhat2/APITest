<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInfo extends Model
{
    protected $table = 'customer_info';
    protected $fillable = ['customer_id', 'avatar', 'date_of_birth', 'address', 'telephone'];

    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }
}
