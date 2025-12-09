<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Status;
use App\Models\Rest;
use App\Models\Requests;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_at',
        'leaving_at',
        'status_id',
        'remarks',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class, 'attendance_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class, 'attendance_id');
    }
}
