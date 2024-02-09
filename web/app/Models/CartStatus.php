<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartStatus extends Model
{
    use HasFactory;

    protected $table = 'bbs_cart_status';
    protected $primaryKey = 'cas_id';

    const CREATED_AT = 'cas_created_at';
    public $timestamps = false;

    protected $fillable = [
        'car_id',
        'cas_status'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'car_id', 'car_id');
    }

    public static function saveCurrentStatus($cart)
    {
        return CartStatus::create([
            'car_id' => $cart->car_id,
            'cas_status' => $cart->car_status
        ]);
    }
}
