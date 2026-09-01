<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StaffWorkTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_work_tasks';

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'employee_id',
        'assigned_by',
        'project_id',
        'department_id',
        'start_date',
        'due_date',
        'start_time',
        'end_time',
        'priority',
        'status',
        'progress',
        'remarks',
        'completion_notes',
        'completed_at',
        'is_approved',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'completed_at' => 'date',
        'progress' => 'integer',
        'is_approved' => 'boolean'
    ];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_ON_HOLD = 'on_hold';

    // Priority Constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get all statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_ON_HOLD => 'On Hold'
        ];
    }

    /**
     * Get all priorities
     */
    public static function getPriorities()
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent'
        ];
    }

    /**
     * Generate unique task number
     */
    public static function generateTaskNumber()
    {
        $latest = self::where('created_by', Auth::user()->creatorId())
            ->latest()
            ->first();
        
        if (!$latest) {
            return 'TASK-0001';
        }
        
        $lastNumber = intval(substr($latest->task_id, 5));
        return 'TASK-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Helper Methods
    public function isOverdue()
    {
        return $this->due_date < now() && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            self::PRIORITY_LOW => 'badge bg-secondary',
            self::PRIORITY_MEDIUM => 'badge bg-info',
            self::PRIORITY_HIGH => 'badge bg-warning',
            self::PRIORITY_URGENT => 'badge bg-danger'
        ];
        
        return '<span class="' . $badges[$this->priority] . '">' . ucfirst($this->priority) . '</span>';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => 'badge bg-secondary',
            self::STATUS_IN_PROGRESS => 'badge bg-info',
            self::STATUS_COMPLETED => 'badge bg-success',
            self::STATUS_CANCELLED => 'badge bg-danger',
            self::STATUS_OVERDUE => 'badge bg-warning',
            self::STATUS_ON_HOLD => 'badge bg-dark'
        ];
        
        return '<span class="' . $badges[$this->status] . '">' . ucfirst(str_replace('_', ' ', $this->status)) . '</span>';
    }

    public function getProgressPercentageAttribute()
    {
        return $this->progress . '%';
    }

    public function getProgressBarAttribute()
    {
        $width = $this->progress;
        return '<div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: ' . $width . '%;"></div>
                </div>';
    }

    // Boot method for auto-generating task number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->task_id)) {
                $model->task_id = self::generateTaskNumber();
            }
            if (empty($model->created_by)) {
                $model->created_by = Auth::user()->creatorId();
            }
        });
    }
}