<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    protected $table = 'relation';
    protected $fillable = ['customer_id_01', 'customer_id_02', 'relation_value'];

    public function customer_01()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id_01', 'customer_id');
    }
    public function customer_02()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id_02', 'customer_id');
    }
}
