<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadEmail extends Model
{
    protected $fillable = [
        'lead_id',
        'to',
        'subject',
        'description',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}