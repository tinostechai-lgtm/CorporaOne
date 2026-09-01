<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'user_id',
        'pipeline_id',
        'stage_id',
        'sources',
        'products',
        'notes',
        'labels',
        'order',
        'created_by',
        'is_active',
        'date',
        'meta_leadgen_id',
        'lead_source',
        // Social media fields
        'facebook_lead_id',
        'instagram_lead_id',
        'whatsapp_lead_id',
        'social_profile_url',
        'social_media_handle',
        'lead_score',
        'assigned_at',
        'converted_at',
        'last_contacted_at',
        'next_follow_up_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_converted' => 'boolean',
        'assigned_at' => 'datetime',
        'converted_at' => 'datetime',
        'last_contacted_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class, 'pipeline_id');
    }

    public function files()
    {
        return $this->hasMany(LeadFile::class, 'lead_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_leads', 'lead_id', 'user_id');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_leads', 'lead_id', 'user_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivityLog::class, 'lead_id')->orderBy('id', 'desc');
    }

    public function discussions()
    {
        return $this->hasMany(LeadDiscussion::class, 'lead_id')->orderBy('id', 'desc');
    }

    public function calls()
    {
        return $this->hasMany(LeadCall::class, 'lead_id');
    }

    public function emails()
    {
        return $this->hasMany(LeadEmail::class, 'lead_id')->orderByDesc('id');
    }

    // Helper methods
    public function getLabels()
    {
        if ($this->labels && !empty($this->labels)) {
            return Label::whereIn('id', explode(',', $this->labels))->get();
        }
        return collect();
    }

    public function getSources()
    {
        if ($this->sources && !empty($this->sources)) {
            return Source::whereIn('id', explode(',', $this->sources))->get();
        }
        return collect();
    }

    public function getProducts()
    {
        if ($this->products && !empty($this->products)) {
            return ProductService::whereIn('id', explode(',', $this->products))->get();
        }
        return collect();
    }

    // Scopes
    public function scopeUnassigned($query)
    {
        return $query->whereDoesntHave('users');
    }

    public function scopeAssigned($query)
    {
        return $query->whereHas('users');
    }

    public function scopeFromSocial($query, $platform = null)
    {
        if ($platform) {
            return $query->where('lead_source', $platform);
        }
        return $query->whereIn('lead_source', ['facebook', 'instagram', 'whatsapp']);
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<=', now());
    }
}