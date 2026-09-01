<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceEmployee extends Model
{
    protected $fillable = [
        // ===== BASIC FIELDS =====
        'employee_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'late',
        'early_leaving',
        'overtime',
        'total_rest',
        'created_by',
        
        // ===== FACE RECOGNITION FIELDS =====
        'face_confidence',      // Face recognition confidence score (0-100)
        'marked_by',            // 'face_recognition' or 'manual'
        
        // ===== LOCATION FIELDS =====
        'latitude',             // GPS latitude
        'longitude',            // GPS longitude
        'location_mode',        // 'office' or 'remote'
        'address',              // Formatted address
        
        // ===== PHOTO FIELDS =====
        'punch_photo',          // Clock in photo
        'punch_out_photo',      // Clock out photo
        'break_in_photo',       // Break start photo
        'break_out_photo',      // Break end photo
        
        // ===== BREAK FIELDS =====
        'tea_break_out',        // Break start time
        'tea_break_in',         // Break end time
        'break_start',          // Alternative break start (legacy)
        'break_end',            // Alternative break end (legacy)
        'punch_state',          // Current state: clock_in, tea_break_out, tea_break_in, clock_out
    ];

    // =================================================================
    // RELATIONSHIPS
    // =================================================================

    /**
     * Get the employee associated with the attendance (via user_id)
     */
    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

    /**
     * Get the employee associated with the attendance (via id)
     */
    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    /**
     * Get the work report associated with this attendance
     */
    public function workReport()
    {
        return $this->hasOne(WorkReport::class, 'attendance_id');
    }

    /**
     * Get the user who created this attendance
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =================================================================
    // STATUS CHECK METHODS
    // =================================================================

    /**
     * Check if employee is clocked in
     */
    public function isClockedIn()
    {
        return $this->clock_in != '00:00:00';
    }

    /**
     * Check if employee is clocked out
     */
    public function isClockedOut()
    {
        return $this->clock_out != '00:00:00';
    }

    /**
     * Check if employee is on break
     */
    public function isOnBreak()
    {
        return !empty($this->tea_break_out) 
            && $this->tea_break_out != '00:00:00'
            && (empty($this->tea_break_in) || $this->tea_break_in == '00:00:00');
    }

    /**
     * Check if employee is late
     */
    public function isLate()
    {
        return $this->late != '00:00:00';
    }

    /**
     * Check if employee left early
     */
    public function isEarlyLeave()
    {
        return $this->early_leaving != '00:00:00';
    }

    /**
     * Check if employee has overtime
     */
    public function hasOvertime()
    {
        return $this->overtime != '00:00:00';
    }

    /**
     * Check if this is a half day
     */
    public function isHalfDay()
    {
        return $this->status == 'Half Day';
    }

    /**
     * Check if attendance was marked by face recognition
     */
    public function isFaceMarked()
    {
        return $this->marked_by == 'face_recognition';
    }

    /**
     * Check if attendance is present
     */
    public function isPresent()
    {
        return $this->status == 'Present';
    }

    /**
     * Check if attendance is on leave
     */
    public function isLeave()
    {
        return $this->status == 'Leave';
    }

    /**
     * Check if employee is currently live (clocked in but not clocked out)
     */
    public function isLive()
    {
        return $this->isClockedIn() && !$this->isClockedOut();
    }

    // =================================================================
    // CALCULATION METHODS
    // =================================================================

    /**
     * Get worked hours in HH:MM:SS format
     */
    public function getWorkedHoursAttribute()
    {
        if ($this->clock_in == '00:00:00') {
            return '00:00:00';
        }
        
        $start = Carbon::parse($this->clock_in);
        $end = $this->clock_out != '00:00:00' 
            ? Carbon::parse($this->clock_out) 
            : Carbon::now();
        
        $diff = $start->diff($end);
        return $diff->format('%H:%I:%S');
    }

    /**
     * Get worked hours in decimal format (e.g., 8.5)
     */
    public function getWorkedHoursDecimalAttribute()
    {
        if ($this->clock_in == '00:00:00') {
            return 0;
        }
        
        $start = Carbon::parse($this->clock_in);
        $end = $this->clock_out != '00:00:00' 
            ? Carbon::parse($this->clock_out) 
            : Carbon::now();
        
        return round($start->diffInMinutes($end) / 60, 2);
    }

    /**
     * Get break duration in HH:MM:SS format
     */
    public function getBreakDurationAttribute()
    {
        if (empty($this->tea_break_out) || $this->tea_break_out == '00:00:00') {
            return '00:00:00';
        }
        
        $end = !empty($this->tea_break_in) && $this->tea_break_in != '00:00:00'
            ? Carbon::parse($this->tea_break_in)
            : Carbon::now();
        
        $start = Carbon::parse($this->tea_break_out);
        $diff = $start->diff($end);
        return $diff->format('%H:%I:%S');
    }

    /**
     * Get break duration in minutes
     */
    public function getBreakDurationMinutesAttribute()
    {
        if (empty($this->tea_break_out) || $this->tea_break_out == '00:00:00') {
            return 0;
        }
        
        $end = !empty($this->tea_break_in) && $this->tea_break_in != '00:00:00'
            ? Carbon::parse($this->tea_break_in)
            : Carbon::now();
        
        $start = Carbon::parse($this->tea_break_out);
        return $start->diffInMinutes($end);
    }

    /**
     * Get late duration in minutes
     */
    public function getLateMinutesAttribute()
    {
        if ($this->late == '00:00:00') {
            return 0;
        }
        
        $lateParts = explode(':', $this->late);
        return ($lateParts[0] * 60) + $lateParts[1];
    }

    /**
     * Get early leaving in minutes
     */
    public function getEarlyLeaveMinutesAttribute()
    {
        if ($this->early_leaving == '00:00:00') {
            return 0;
        }
        
        $parts = explode(':', $this->early_leaving);
        return ($parts[0] * 60) + $parts[1];
    }

    /**
     * Get overtime in minutes
     */
    public function getOvertimeMinutesAttribute()
    {
        if ($this->overtime == '00:00:00') {
            return 0;
        }
        
        $otParts = explode(':', $this->overtime);
        return ($otParts[0] * 60) + $otParts[1];
    }

    /**
     * Get total working minutes (excluding breaks)
     */
    public function getTotalWorkingMinutesAttribute()
    {
        if ($this->clock_in == '00:00:00') {
            return 0;
        }
        
        $totalMinutes = $this->worked_hours_decimal * 60;
        $breakMinutes = $this->break_duration_minutes;
        
        return max(0, $totalMinutes - $breakMinutes);
    }

    /**
     * Get total working hours in decimal (excluding breaks)
     */
    public function getTotalWorkingHoursAttribute()
    {
        return round($this->total_working_minutes / 60, 2);
    }

    // =================================================================
    // STATUS LABEL METHODS
    // =================================================================

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute()
    {
        if ($this->clock_in == '00:00:00') {
            return 'Not Clocked In';
        }
        
        if ($this->isHalfDay()) {
            return 'Half Day';
        }
        
        if ($this->isOnBreak()) {
            return 'On Break';
        }
        
        if ($this->clock_out != '00:00:00') {
            if ($this->isEarlyLeave()) {
                return 'Early Leave';
            }
            return 'Clocked Out';
        }
        
        if ($this->isLate()) {
            return 'Late In';
        }
        
        return 'Clocked In';
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $status = $this->status_label;
        
        $colors = [
            'Not Clocked In' => 'secondary',
            'Half Day' => 'warning',
            'On Break' => 'info',
            'Clocked Out' => 'primary',
            'Early Leave' => 'danger',
            'Late In' => 'warning',
            'Clocked In' => 'success',
        ];
        
        $color = $colors[$status] ?? 'secondary';
        return "<span class='badge bg-{$color}'>{$status}</span>";
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        return match($this->status_label) {
            'Not Clocked In' => 'fa-clock',
            'Half Day' => 'fa-sun',
            'On Break' => 'fa-coffee',
            'Clocked Out' => 'fa-sign-out-alt',
            'Early Leave' => 'fa-exit',
            'Late In' => 'fa-exclamation-triangle',
            'Clocked In' => 'fa-check-circle',
            default => 'fa-circle'
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status_label) {
            'Not Clocked In' => '#6c757d',
            'Half Day' => '#ffc107',
            'On Break' => '#17a2b8',
            'Clocked Out' => '#007bff',
            'Early Leave' => '#dc3545',
            'Late In' => '#ffc107',
            'Clocked In' => '#28a745',
            default => '#6c757d'
        };
    }

    // =================================================================
    // PHOTO HELPERS
    // =================================================================

    /**
     * Get punch in photo URL
     */
    public function getPunchPhotoUrlAttribute()
    {
        if (empty($this->punch_photo)) {
            return null;
        }
        return asset('uploads/attendance/' . $this->punch_photo);
    }

    /**
     * Get punch out photo URL
     */
    public function getPunchOutPhotoUrlAttribute()
    {
        if (empty($this->punch_out_photo)) {
            return null;
        }
        return asset('uploads/attendance/' . $this->punch_out_photo);
    }

    /**
     * Get break in photo URL
     */
    public function getBreakInPhotoUrlAttribute()
    {
        if (empty($this->break_in_photo)) {
            return null;
        }
        return asset('uploads/attendance/' . $this->break_in_photo);
    }

    /**
     * Get break out photo URL
     */
    public function getBreakOutPhotoUrlAttribute()
    {
        if (empty($this->break_out_photo)) {
            return null;
        }
        return asset('uploads/attendance/' . $this->break_out_photo);
    }

    /**
     * Get all photos as array
     */
    public function getPhotosAttribute()
    {
        return [
            'punch_in' => $this->punch_photo_url,
            'punch_out' => $this->punch_out_photo_url,
            'break_in' => $this->break_in_photo_url,
            'break_out' => $this->break_out_photo_url,
        ];
    }

    /**
     * Check if any photos exist
     */
    public function getHasPhotosAttribute()
    {
        return !empty($this->punch_photo) 
            || !empty($this->punch_out_photo)
            || !empty($this->break_in_photo)
            || !empty($this->break_out_photo);
    }

    // =================================================================
    // LOCATION HELPERS
    // =================================================================

    /**
     * Check if location is verified (within office radius)
     */
    public function isLocationVerified()
    {
        // This would need the office location from settings
        // You can implement this based on your business logic
        return true;
    }

    /**
     * Get formatted address
     */
    public function getFormattedAddressAttribute()
    {
        if (empty($this->address)) {
            return null;
        }
        return $this->address;
    }

    /**
     * Get Google Maps link for location
     */
    public function getGoogleMapsLinkAttribute()
    {
        if (empty($this->latitude) || empty($this->longitude)) {
            return null;
        }
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    // =================================================================
    // DATE FORMATTING HELPERS
    // =================================================================

    /**
     * Get formatted clock in time
     */
    public function getFormattedClockInAttribute()
    {
        if ($this->clock_in == '00:00:00') {
            return '--:--';
        }
        return Carbon::parse($this->clock_in)->format('h:i A');
    }

    /**
     * Get formatted clock out time
     */
    public function getFormattedClockOutAttribute()
    {
        if ($this->clock_out == '00:00:00') {
            return '--:--';
        }
        return Carbon::parse($this->clock_out)->format('h:i A');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->format('d M Y');
    }

    /**
     * Get date with day name
     */
    public function getDateWithDayAttribute()
    {
        return Carbon::parse($this->date)->format('l, d M Y');
    }

    /**
     * Get formatted created at
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d M Y h:i A') : 'N/A';
    }

    /**
     * Get formatted updated at
     */
    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d M Y h:i A') : 'N/A';
    }

    // =================================================================
    // SCOPE METHODS
    // =================================================================

    /**
     * Scope for today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope for a specific date
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope for a specific employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('created_by', $companyId);
    }

    /**
     * Scope for clocked in employees
     */
    public function scopeClockedIn($query)
    {
        return $query->where('clock_in', '!=', '00:00:00')
                     ->where('clock_out', '00:00:00');
    }

    /**
     * Scope for clocked out employees
     */
    public function scopeClockedOut($query)
    {
        return $query->where('clock_out', '!=', '00:00:00');
    }

    /**
     * Scope for on break employees
     */
    public function scopeOnBreak($query)
    {
        return $query->where('tea_break_out', '!=', '00:00:00')
                     ->where(function($q) {
                         $q->where('tea_break_in', '00:00:00')
                           ->orWhereNull('tea_break_in');
                     });
    }

    /**
     * Scope for late employees
     */
    public function scopeLate($query)
    {
        return $query->where('late', '!=', '00:00:00');
    }

    /**
     * Scope for half day employees
     */
    public function scopeHalfDay($query)
    {
        return $query->where('status', 'Half Day');
    }

    /**
     * Scope for face marked attendance
     */
    public function scopeFaceMarked($query)
    {
        return $query->where('marked_by', 'face_recognition');
    }

    /**
     * Scope for present employees
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'Present');
    }

    /**
     * Scope for live employees (clocked in, not clocked out)
     */
    public function scopeLive($query)
    {
        return $query->where('clock_in', '!=', '00:00:00')
                     ->where('clock_out', '00:00:00');
    }

    /**
     * Scope for employees by punch state
     */
    public function scopeByPunchState($query, $state)
    {
        return $query->where('punch_state', $state);
    }

    /**
     * Scope for employees by location mode
     */
    public function scopeByLocationMode($query, $mode)
    {
        return $query->where('location_mode', $mode);
    }

    // =================================================================
    // JSON SERIALIZATION
    // =================================================================

    /**
     * Get the model's attributes for API response
     */
    public function toApiResponse()
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->name,
            'date' => $this->date,
            'formatted_date' => $this->formatted_date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'formatted_clock_in' => $this->formatted_clock_in,
            'formatted_clock_out' => $this->formatted_clock_out,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_badge' => $this->status_badge,
            'is_clocked_in' => $this->isClockedIn(),
            'is_clocked_out' => $this->isClockedOut(),
            'is_on_break' => $this->isOnBreak(),
            'is_late' => $this->isLate(),
            'is_early_leave' => $this->isEarlyLeave(),
            'is_half_day' => $this->isHalfDay(),
            'is_face_marked' => $this->isFaceMarked(),
            'is_present' => $this->isPresent(),
            'is_live' => $this->isLive(),
            'late' => $this->late,
            'late_minutes' => $this->late_minutes,
            'early_leaving' => $this->early_leaving,
            'early_leave_minutes' => $this->early_leave_minutes,
            'overtime' => $this->overtime,
            'overtime_minutes' => $this->overtime_minutes,
            'worked_hours' => $this->worked_hours,
            'worked_hours_decimal' => $this->worked_hours_decimal,
            'break_duration' => $this->break_duration,
            'break_duration_minutes' => $this->break_duration_minutes,
            'total_working_minutes' => $this->total_working_minutes,
            'total_working_hours' => $this->total_working_hours,
            'face_confidence' => $this->face_confidence,
            'marked_by' => $this->marked_by,
            'has_photos' => $this->has_photos,
            'photos' => $this->photos,
            'location_mode' => $this->location_mode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'google_maps_link' => $this->google_maps_link,
            'punch_state' => $this->punch_state,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'formatted_created_at' => $this->formatted_created_at,
            'formatted_updated_at' => $this->formatted_updated_at,
        ];
    }

    /**
     * Get the model's attributes for admin panel
     */
    public function toAdminResponse()
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->name,
            'employee_email' => $this->employee?->email,
            'date' => $this->date,
            'formatted_date' => $this->formatted_date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'formatted_clock_in' => $this->formatted_clock_in,
            'formatted_clock_out' => $this->formatted_clock_out,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_badge' => $this->status_badge,
            'late' => $this->late,
            'early_leaving' => $this->early_leaving,
            'overtime' => $this->overtime,
            'worked_hours' => $this->worked_hours,
            'break_duration' => $this->break_duration,
            'face_confidence' => $this->face_confidence,
            'marked_by' => $this->marked_by,
            'location_mode' => $this->location_mode,
            'has_photos' => $this->has_photos,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    // =================================================================
    // BOOT METHOD - Auto-set created_by
    // =================================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->user()->creatorId();
            }
        });
    }
}