<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $table = 'bbs_api_log';
    protected $primaryKey = 'alg_id';

    public $timestamps = false;

    protected $fillable = [
        'alg_external_id',
        'alg_url',
        'alg_request',
        'alg_response',
        'alg_status_code',
        'alg_created_at',
        'alg_updated_at'
    ];
    /**
     * Functions
     */
    public static function storeLog($orderFlowId, $url, $request)
    {
        return ApiLog::create([
            'alg_external_id' => $orderFlowId,
            'alg_url' => $url,
            'alg_request' => json_encode($request),
            'alg_created_at' =>now()
        ]);
    }

    public function updateLog($response, $status)
    {
        $this->update([
            'alg_response' => json_encode($response),
            'alg_status_code' => $status,
            'alg_updated_at' =>now()
        ]);
    }
}
