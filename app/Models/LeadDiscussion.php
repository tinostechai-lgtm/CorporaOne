<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDiscussion extends Model
{
    protected $fillable = [
        'lead_id',
        'comment',
        'created_by',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}