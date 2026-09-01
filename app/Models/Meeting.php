<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'title',
        'date',
        'time',
        'duration',
        'description',
        'created_by',
    ];

    public function meetingEmployees()
    {
        return $this->belongsToMany(Employee::class, 'meeting_employees', 'meeting_id', 'employee_id')
                    ->withTimestamps();
    }
}