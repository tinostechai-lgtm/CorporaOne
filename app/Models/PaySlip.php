<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlip extends Model
{
    protected $fillable = [
        'employee_id',
        'net_payble',
        'basic_salary',
        'salary_month',
        'status',
        'allowance',
        'commission',
        'loan',
        'saturation_deduction',
        'other_payment',
        'overtime',
        'created_by',
    ];

    /**
     * Relationship: A payslip belongs to an employee.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
    }
}