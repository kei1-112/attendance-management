<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    public function attendance(){
        return $this->hasOne(Attendance::class);
    }
}
