<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart_status extends Model
{
    use HasFactory;

    protected $table = 'bbs_cart_status';
    protected $primaryKey = 'cas_id';

    protected $fillable = [
        'cas_status',
        'cas_created_at'
    ];

    /**
     * Relationships
     */ 
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'car_id', 'car_id');
    }


    /**
     * Functions
     */ 
    public static function saveCurrentStatus($cart)
    {
        return Cart_status::create([
            'car_id' => $cart->car_id,
            'car_status' => $cart->car_status
        ]);
    }
}
