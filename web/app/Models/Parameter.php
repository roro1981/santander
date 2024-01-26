<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use HasFactory;

    protected $table = 'bbs_parameter';

    public $timestamps = false;

    protected $fillable = [
        'par_code',
        'par_value',
        'par_description',
        'par_created_at',
        'par_updated_at'
    ];
}
