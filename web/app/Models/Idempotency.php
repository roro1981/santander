<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idempotency extends Model
{
    use HasFactory;

    protected $table = 'bbs_idempotency';
    protected $primaryKey = 'idp_uuid';

    protected $fillable = [
        'idp_response',
        'idp_httpcode',
        'idp_created_at',
        'idp_updated_at'
    ];
}
