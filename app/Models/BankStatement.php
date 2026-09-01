<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_number',
        'account_holder_name',
        'statement_date',
        'file_path',
        'file_name',
        'reconciliation_status',
        'opening_balance',
        'closing_balance',
        'created_by'
    ];

    protected $casts = [
        'statement_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    /**
     * Get all transactions for this statement
     */
    public function transactions()
    {
        return $this->hasMany(BankStatementTransaction::class);
    }

    /**
     * Get the user who created the statement
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include statements for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}