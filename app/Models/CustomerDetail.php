<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDetail extends Model
{
    /**
     * The database table role by the model.
     *
     * @var string
     */
    protected $table = 'customer_detail';
    protected $fillable = ['customer_id', 'name', 'email', 'password', 'verify_code'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
