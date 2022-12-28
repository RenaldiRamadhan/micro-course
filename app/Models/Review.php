<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $table = 'reviews';
    
    protected $fillable = [
        'user_id', 'course_id', 'rating', 'note'
    ];

    
    public function course()
    {
        return $this->belongsTo('App\Course');
    }

}
