<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $primaryKey = 'store_id';
    protected $fillable = [
        'store_name',
    ];

    public function consignments()
    {
        return $this->hasMany(Consignment::class, 'store_id', 'store_id');
    }

}
