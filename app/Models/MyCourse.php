<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyCourse extends Model
{

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $table = 'my_courses';
    
    protected $fillable = [
        'course_id', 'user_id'
    ];

    public function course()
    {
        return $this->belongsTo('App\Models\Course');
    }
}
