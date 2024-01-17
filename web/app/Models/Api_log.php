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
    /**
     * Functions
     */
    public static function storeLog($orderFlowId, $bankId, $url, $request)
    {
        return store::create([
            'alg_external_id' => $orderFlowId,
            'alg_bank_id' => $bankId,
            'alg_url' => $url,
            'alg_request' => json_encode($request),
        ]);
    }

    public function updateLog($response, $status, $bankId = null)
    {
        $this->update([
            'alg_response' => json_encode($response),
            'alg_status_code' => $status,
            'alg_bank_id' => $bankId ?? $this->alg_bank_id
        ]);
    }
}
