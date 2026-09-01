<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'priority',
        'start_date',
        'end_date', 
        'status',
        'is_complete',
        'assign_to',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assign_to' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public const PRIORITY = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];

    public const PRIORITY_COLOR = [
        'low' => 'success',
        'medium' => 'info', 
        'high' => 'warning',
        'critical' => 'danger',
    ];

    public const STATUS = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function assignees()
    {
        return User::whereIn('id', $this->assign_to ?? [])->get();
    }

    public function scopeByCompany($query)
    {
        return $query->where('created_by', auth()->user()->creatorId());
    }

    public function scopeOverdue($query)
    {
        return $query->where('end_date', '<', now()->format('Y-m-d'))
                     ->where('is_complete', 0);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereRaw("JSON_CONTAINS(assign_to, ?)", [$userId]);
    }
}

