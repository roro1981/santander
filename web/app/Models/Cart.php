<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'bbs_cart';
    protected $primaryKey = 'car_id';

    protected $fillable = [
        'car_uuid',
        'car_id_transaction',
        'car_currency',
        'car_amount',
        'car_description',
        'car_agreement',
        'car_url',
        'car__items_number',
        'car_collector',
        'car_status',
        'car_url_return',
        'car_authorization_uuid',
        'car_sent_kafka',
        'car_fail_code',
        'car_fail_motive',
        'car_created_at',
        'car_updated_at'
     ];

     protected $hidden = [
        'car_id'
     ];

     /**
     * Functions
     */
     public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->car_uuid = (string) Str::uuid();
        });
    }   
    /**
     * Relationships
     */
    public function cart_details()
    {
        return $this->hasMany(Cart_detail::class, 'car_id', 'car_id');
    }

    public function cart_additional_data()
    {
        return $this->hasMany(Cart_additional_data::class, 'car_id', 'car_id');
    }

    public function cart_status()
    {
        return $this->hasMany(Cart_status::class, 'car_id', 'car_id');
    }
}
