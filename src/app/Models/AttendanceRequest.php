<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'approval_status',
        'request_date',
        'new_date',
        'new_clock_in',
        'new_clock_out',
        'new_break_in',
        'new_break_out',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function newBreaks()
    {
        return $this->hasMany(NewBreak::class, 'attendance_request_id');
    }
}
