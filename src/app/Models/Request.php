<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'requested_at',
        'approval_flag',
    ];

    public function attendance(){
        return $this->belongsTo(Attendance::class);
    }
}
