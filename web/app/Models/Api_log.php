<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Api_log extends Model
{
    use HasFactory;

    protected $table = 'bbs_api_log';
    protected $primaryKey = 'alg_id';

    protected $fillable = [
        'alg_external_id',
        'alg_url',
        'alg_request',
        'alg_response',
        'alg_status_code',
        'alg_created_at'
    ];
}
