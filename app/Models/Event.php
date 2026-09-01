<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'start_date',
        'end_date',
        'color',
        'description',
        'created_by',
    ];

    /**
     * Get the employee who owns/created the event (if single owner).
     * This is optional, but often used.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * The employees that are assigned to this event (many-to-many).
     * This is what your controller's ->with('eventEmployees') expects.
     */
    public function eventEmployees()
    {
        return $this->belongsToMany(Employee::class, 'event_employees', 'event_id', 'employee_id')
                    ->withTimestamps(); // if your pivot table has timestamps
    }
}