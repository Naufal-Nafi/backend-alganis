<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Consignment extends Model
{
    use HasFactory;

    protected $primaryKey = 'consignment_id';
    protected $fillable = [
        'product_id',
        'store_id',
        'user_id',
        'creator_name',
        'entry_date',
        'exit_date',
        'stock',
        'sold',        
    ];

    protected $dates = [
        'exit_date',
        'entry_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'store_id');
    }
    
    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getCirculationDurationAttribute()
    {
        return $this->entry_date
            ? $this->exit_date->diffInDays($this->entry_date)
            : null;
    }

    public function getStatusAttribute()
    {
        return $this->entry_date ? 'close' : 'open';
    }


    public function getPendapatan()
    {
        return $this->sold * $this->product->price;
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function ($consignment) {
    //         if ($consignment->exit_date && $consignment->entry_date) {
    //             $diffInDays = $consignment->exit_date->diffInDays($consignment->entry_date);

    //             $consignment->status = $diffInDays >= 7 ? 'close' : 'open';
    //         }
    //     });
    // }


}
