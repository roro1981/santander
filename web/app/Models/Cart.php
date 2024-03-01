<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use App\Http\Utils\Util;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'bbs_cart';
    protected $primaryKey = 'car_id';
    const CREATED_AT = 'car_created_at';
    const UPDATED_AT = 'car_updated_at';

    protected $fillable = [
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
        'car_flow_email_paid'
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
        /*static::creating(function ($model) {
            $model->car_id = 169008;
        });*/
    }   

    public static function storeCart($uuid, $orderRequest, $userRequest)
    {
        return Cart::create([
            'car_id_transaction' => $uuid,
            'car_flow_currency' => $orderRequest['currency'],
            'car_flow_amount' => $orderRequest['amount'],
            'car_url' => $orderRequest['url_confirmation'],
            'car_expires_at' => Util::validateExpirationTime($orderRequest['expiration']),
            'car_items_number' => 1,
            'car_status' => Constants::STATUS_CREATED,
            'car_url_return' => ParamUtil::getParam(Constants::PARAM_URL_RETORNO),
            'car_sent_kafka' => 0,
            'car_flow_id' => $orderRequest['id'],
            'car_flow_attempt_number' => $orderRequest['attempt_number'],
            'car_flow_product_id' => $orderRequest['product_id'],
            'car_flow_email_paid' => $userRequest['email'],
            'car_flow_subject' => $orderRequest['subject'],
            'car_created_at' => now()
        ]);
    }

    /**
     * Relationships
     */

    public function cartStatus()
    {
        return $this->hasMany(CartStatus::class, 'car_id', 'car_id');
    }

    
}
