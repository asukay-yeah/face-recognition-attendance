<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FaceEncoding extends Model
{
    protected $fillable = [
        'user_id', 'encoding', 'image_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
