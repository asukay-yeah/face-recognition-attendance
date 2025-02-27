<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'date', 'check_in', 'check_out', 'status', 'note' 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
