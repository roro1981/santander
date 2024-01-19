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

    public $timestamps = false;

    protected $fillable = [
        'car_id',
        'car_uuid',
        'car_id_transaction',
        'car_flow_currency',
        'car_flow_amount',
        'car_description',
        'car_agreement',
        'car_url',
        'car_expires_at',
        'car_items_number',
        'car_collector',
        'car_status',
        'car_url_return',
        'car_authorization_uuid',
        'car_sent_kafka',
        'car_fail_code',
        'car_fail_motive',
        'car_flow_id',
        'car_flow_attempt_number',
        'car_flow_product_id',
        'car_flow_subject',
        'car_flow_email_paid',
        'car_created_at'
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

    public function cart_status()
    {
        return $this->hasMany(Cart_status::class, 'car_id', 'car_id');
    }
}
