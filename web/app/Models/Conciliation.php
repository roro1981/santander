<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conciliation extends Model
{
    protected $table = 'bbs_conciliation'; 

    protected $primaryKey = 'con_id';

    public $timestamps = false;

    const CREATED_AT = 'con_created_at';

    protected $fillable = [
        'con_cart_id',
        'con_agreement_id',
        'con_product_number',
        'con_customer_number',
        'con_product_expiration',
        'con_product_description',
        'con_product_amount',
        'con_operation_number',
        'con_operation_date',
        'con_status',
        'con_file_process'
    ];
    
}
