<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'product_id';
    protected $fillable = [
        'product_name',
        'price',        
    ];

    public function consignments()
    {
        return $this->hasMany(Consignment::class, 'product_id', 'product_id');
    }
}
