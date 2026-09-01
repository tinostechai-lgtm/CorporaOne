<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'branch_id',
        'department_id',
        'description',
        'created_by',
    ];

    /**
     * Relationship: Employees assigned to this announcement (many-to-many)
     */
    public function announcementEmployees()
    {
        return $this->belongsToMany(
            Employee::class,               // Related model
            'announcement_employees',      // Pivot table name
            'announcement_id',             // Foreign key on pivot
            'employee_id'                  // Related key on pivot
        );
    }
}