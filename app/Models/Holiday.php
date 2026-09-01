<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'occasion',
        'date',
        'end_date',
        'holiday_type',
        'week_off_days',
        'week_off_applicable',
        'leave_type_id',
        'leave_duration',
        'leave_date_from',
        'leave_date_to',
        'leave_reason',
        'is_paid',
        'applicable_to',
        'departments',
        'description',
        'synchronize_type',
        'created_by',
        'is_recurring' // Add this to migration
    ];

    protected $casts = [
        'departments' => 'array',
        'week_off_days' => 'array',
        'is_paid' => 'boolean',
        'date' => 'date',
        'end_date' => 'date',
        'leave_date_from' => 'date',
        'leave_date_to' => 'date',
        'is_recurring' => 'boolean',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    // ============================================================
    // ACCESSORS
    // ============================================================
    public function getTypeLabelAttribute()
    {
        $labels = [
            'holiday' => 'Holiday',
            'week_off' => 'Week Off',
            'paid_leave' => 'Paid Leave',
            'unpaid_leave' => 'Unpaid Leave',
            'sick_leave' => 'Sick Leave',
            'casual_leave' => 'Casual Leave',
            'maternity_leave' => 'Maternity Leave',
            'paternity_leave' => 'Paternity Leave',
            'compensatory_off' => 'Compensatory Off',
            'other' => 'Other',
        ];
        return $labels[$this->type] ?? 'Unknown';
    }

    /**
     * Get week off days as array
     */
    public function getWeekOffDaysArray()
    {
        if (is_array($this->week_off_days)) {
            return $this->week_off_days;
        }
        return json_decode($this->week_off_days, true) ?? [];
    }

    /**
     * Get week off days names for display
     */
    public function getWeekOffDaysLabelAttribute()
    {
        if (empty($this->week_off_days)) {
            return 'None';
        }
        
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
        
        $selected = [];
        $weekOffDays = $this->getWeekOffDaysArray();
        
        if (is_array($weekOffDays)) {
            foreach ($weekOffDays as $day) {
                if (isset($days[$day])) {
                    $selected[] = $days[$day];
                }
            }
        }
        
        return implode(', ', $selected);
    }

    /**
     * Get display date based on type
     */
    public function getDisplayDateAttribute()
    {
        if ($this->isWeekOff()) {
            $days = $this->getWeekOffDaysArray();
            $dayNames = [
                1 => 'Monday',
                2 => 'Tuesday', 
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday'
            ];
            
            $names = [];
            foreach ($days as $day) {
                if (isset($dayNames[$day])) {
                    $names[] = $dayNames[$day];
                }
            }
            
            return 'Every ' . implode(', ', $names);
        }
        
        if ($this->date && $this->end_date) {
            if ($this->date->format('Y-m-d') == $this->end_date->format('Y-m-d')) {
                return $this->date->format('d M Y');
            }
            return $this->date->format('d M Y') . ' - ' . $this->end_date->format('d M Y');
        }
        
        return 'N/A';
    }

    public function getIsPaidLabelAttribute()
    {
        return $this->is_paid ? 'Yes' : 'No';
    }

    /**
     * FIXED: Get status with proper handling for week off
     */
    public function getStatusAttribute()
    {
        // For week off, it's always active/recurring
        if ($this->isWeekOff()) {
            return 'Active';
        }

        // For regular holidays/leaves
        $today = now()->format('Y-m-d');
        
        if (!$this->date || !$this->end_date) {
            return 'N/A';
        }
        
        $date = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : date('Y-m-d', strtotime($this->date));
        $endDate = $this->end_date instanceof Carbon ? $this->end_date->format('Y-m-d') : date('Y-m-d', strtotime($this->end_date));
        
        if ($endDate < $today) {
            return 'Past';
        } elseif ($date <= $today && $endDate >= $today) {
            return 'Ongoing';
        } else {
            return 'Upcoming';
        }
    }

    /**
     * Get status color for badge
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Active' => 'success',
            'Past' => 'secondary',
            'Ongoing' => 'warning',
            'Upcoming' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Check if week off is active for a given date
     */
    public function isWeekOffOnDate($date)
    {
        if (!$this->isWeekOff()) {
            return false;
        }
        
        $dayOfWeek = date('N', strtotime($date));
        $weekOffDays = $this->getWeekOffDaysArray();
        
        return in_array($dayOfWeek, $weekOffDays);
    }

    /**
     * Check if holiday applies to a department
     */
    public function isApplicableToDepartment($departmentId)
    {
        if ($this->applicable_to === 'all') {
            return true;
        }
        
        $departments = $this->departments;
        if (is_string($departments)) {
            $departments = json_decode($departments, true);
        }
        
        return in_array($departmentId, $departments ?? []);
    }

    // ============================================================
    // SCOPES
    // ============================================================
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeHolidays($query)
    {
        return $query->where('type', 'holiday');
    }

    public function scopeWeekOffs($query)
    {
        return $query->where('type', 'week_off');
    }

    public function scopeLeaves($query)
    {
        return $query->whereIn('type', ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other']);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', 1);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', 0);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->where('date', '>=', $startDate)
                     ->where('end_date', '<=', $endDate);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', '<=', $date)
                     ->where('end_date', '>=', $date);
    }

    public function scopeActiveWeekOffs($query)
    {
        return $query->where('type', 'week_off');
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================
    public function isHoliday()
    {
        return $this->type === 'holiday';
    }

    public function isWeekOff()
    {
        return $this->type === 'week_off';
    }

    public function isLeave()
    {
        return in_array($this->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other']);
    }

    public function isPaid()
    {
        return (bool) $this->is_paid;
    }

    public function isRecurring()
    {
        return (bool) $this->is_recurring;
    }

    public function getDaysCount()
    {
        if (!$this->date || !$this->end_date) {
            return 0;
        }
        
        $start = Carbon::parse($this->date);
        $end = Carbon::parse($this->end_date);
        return $start->diffInDays($end) + 1;
    }

    public function getDurationLabel()
    {
        $durations = [
            'full_day' => 'Full Day',
            'half_day' => 'Half Day',
            'first_half' => 'First Half',
            'second_half' => 'Second Half',
        ];
        return $durations[$this->leave_duration] ?? $this->leave_duration;
    }

    // ============================================================
    // BOOT METHOD
    // ============================================================
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_by)) {
                $model->created_by = auth()->user()->creatorId();
            }
            
            // Auto-set recurring flag for week off
            if ($model->type === 'week_off') {
                $model->is_recurring = true;
            }
        });
    }
}