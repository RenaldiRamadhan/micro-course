<?php

namespace App\Models;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'name', 'certificate', 'thumbnail', 'type', 'status', 'price', 'level', 'description', 'mentor_id'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function mentor()
    {
        return $this->belongsTo('App\Models\Mentor');
    }

    public function chapters()
    {
        return $this->hasMany('App\Models\Chapter')->orderBy('id', 'ASC');
    }

    public function images ()
    {
        return $this->hasMany('App\Models\ImageCourse')->orderBy('id', 'DESC');

    }
}
