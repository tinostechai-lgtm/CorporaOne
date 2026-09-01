<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkReport extends Model
{
    use HasFactory;

    protected $table = 'work_reports';

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'date',
        'clock_in',
        'clock_out',
        'work_description',
        'quick_tasks',
        'achievements',
        'challenges',
        'tomorrow_plan',
        'hours_project',
        'hours_meeting',
        'hours_admin',
        'review_status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'created_by',
        'attachment'
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'reviewed_at' => 'datetime',
        'hours_project' => 'decimal:1',
        'hours_meeting' => 'decimal:1',
        'hours_admin' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the employee associated with the work report
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the user (employee) associated with the work report (legacy)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the attendance record associated with the work report
     */
    public function attendance()
    {
        return $this->belongsTo(AttendanceEmployee::class, 'attendance_id');
    }

    /**
     * Get the reviewer who reviewed the work report
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the creator of the work report
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the quick tasks as an array
     */
    public function getQuickTasksArrayAttribute()
    {
        if (empty($this->quick_tasks)) {
            return [];
        }
        return array_map('trim', explode(',', $this->quick_tasks));
    }

    /**
     * Get the status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->review_status) {
            'pending' => '<span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i> Pending</span>',
            'approved' => '<span class="badge bg-success"><i class="ti ti-check me-1"></i> Approved</span>',
            'rejected' => '<span class="badge bg-danger"><i class="ti ti-x me-1"></i> Rejected</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    /**
     * Get the status text
     */
    public function getStatusTextAttribute()
    {
        return match($this->review_status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown'
        };
    }

    /**
     * Get the status color
     */
    public function getStatusColorAttribute()
    {
        return match($this->review_status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get total hours worked (sum of all categories)
     */
    public function getTotalHoursAttribute()
    {
        return ($this->hours_project ?? 0) + ($this->hours_meeting ?? 0) + ($this->hours_admin ?? 0);
    }

    /**
     * Get formatted total hours
     */
    public function getFormattedTotalHoursAttribute()
    {
        $total = $this->total_hours;
        return number_format($total, 1) . ' hrs';
    }

    /**
     * Get the attachment URL
     */
    public function getAttachmentUrlAttribute()
    {
        if (empty($this->attachment)) {
            return null;
        }
        return asset('storage/' . $this->attachment);
    }

    /**
     * Check if the report has an attachment
     */
    public function getHasAttachmentAttribute()
    {
        return !empty($this->attachment);
    }

    /**
     * Scope a query to only include reports for a specific employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope a query to only include reports for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to only include reports for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope a query to only include pending reports
     */
    public function scopePending($query)
    {
        return $query->where('review_status', 'pending');
    }

    /**
     * Scope a query to only include approved reports
     */
    public function scopeApproved($query)
    {
        return $query->where('review_status', 'approved');
    }

    /**
     * Scope a query to only include rejected reports
     */
    public function scopeRejected($query)
    {
        return $query->where('review_status', 'rejected');
    }

    /**
     * Scope a query to only include reports for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('created_by', $companyId);
    }

    /**
     * Check if the report is pending review
     */
    public function isPending()
    {
        return $this->review_status === 'pending';
    }

    /**
     * Check if the report is approved
     */
    public function isApproved()
    {
        return $this->review_status === 'approved';
    }

    /**
     * Check if the report is rejected
     */
    public function isRejected()
    {
        return $this->review_status === 'rejected';
    }

    /**
     * Check if the report can be edited
     */
    public function isEditable()
    {
        return $this->isPending() && !$this->reviewed_at;
    }

    /**
     * Get the employee name
     */
    public function getEmployeeNameAttribute()
    {
        return $this->employee ? $this->employee->name : 'N/A';
    }

    /**
     * Get the employee email
     */
    public function getEmployeeEmailAttribute()
    {
        return $this->employee ? $this->employee->email : 'N/A';
    }

    /**
     * Get the reviewer name
     */
    public function getReviewerNameAttribute()
    {
        return $this->reviewer ? $this->reviewer->name : 'Not Reviewed';
    }

    /**
     * Get formatted clock in time
     */
    public function getFormattedClockInAttribute()
    {
        if (empty($this->clock_in)) {
            return '--:--';
        }
        return date('h:i A', strtotime($this->clock_in));
    }

    /**
     * Get formatted clock out time
     */
    public function getFormattedClockOutAttribute()
    {
        if (empty($this->clock_out)) {
            return '--:--';
        }
        return date('h:i A', strtotime($this->clock_out));
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('d M Y') : 'N/A';
    }

    /**
     * Get submitted at (created_at) formatted
     */
    public function getSubmittedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d M Y h:i A') : 'N/A';
    }

    /**
     * Get reviewed at formatted
     */
    public function getReviewedAtFormattedAttribute()
    {
        return $this->reviewed_at ? $this->reviewed_at->format('d M Y h:i A') : 'Not Reviewed';
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set employee_id and created_by when creating
        static::creating(function ($model) {
            if (empty($model->employee_id) && auth()->check()) {
                $employee = auth()->user()->employee;
                if ($employee) {
                    $model->employee_id = $employee->id;
                }
            }
            
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->user()->creatorId();
            }
        });
    }
}