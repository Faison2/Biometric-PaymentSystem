<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacialData extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'facial_descriptor', 'face_image_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}