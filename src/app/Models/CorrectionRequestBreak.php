<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequestBreak extends Model
{
    protected $fillable = [
        'correction_request_id',
        'break_start',
        'break_end',
    ];
}
