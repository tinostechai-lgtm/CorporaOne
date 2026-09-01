<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appraisal extends Model
{
    protected $fillable = [
        'branch',
        'employee',
        'appraisal_date',
        'customer_experience',
        'marketing',
        'administration',
        'professionalism',
        'integrity',
        'attendance',
        'remark',
        'created_by',
        'rating',
    ];

    public static $technical = [
        'None',
        'Beginner',
        'Intermediate',
        'Advanced',
        'Expert / Leader',
    ];

    public static $organizational = [
        'None',
        'Beginner',
        'Intermediate',
        'Advanced',
    ];

    // ✅ Belongs to Branch (foreign key: branch)
    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch');
    }

    // ✅ Belongs to Employee (foreign key: employee)
    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee');
    }

    // Optional alias for singular
    public function employee()
    {
        return $this->employees();
    }
}