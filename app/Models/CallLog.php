<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    protected $fillable = [
        'provider',
        'call_uuid',
        'called_number',
        'caller_number',
        'source_number',
        'destination_number',
        'display_number',
        'data_source',
        'call_type',
        'account_id',
        'leg',
        'agent_status',
        'event_id',
        'callback_parent_id',
        'callback_params',
        'agent_number',
        'status',
        'direction',
        'duration',
        'recording_url',
        'start_time',
        'end_time',
        'dtmf',
        'transferred_number',
        'raw_payload',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'callback_params' => 'array',
        'raw_payload' => 'array',
    ];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}