<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatementTransaction extends Model
{
    use HasFactory;

    protected $table = 'bank_statement_transactions';

    protected $fillable = [
        'bank_statement_id',
        'transaction_id',
        'date',
        'description',
        'purpose',
        'account_name',
        'debit',
        'credit',
        'balance',
        'type',
        'reference_number',
        'cheque_number',
        'matched_with',
        'matched_at',
        'reconciliation_status',
        'raw_data'
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
        'matched_at' => 'datetime',
        'raw_data' => 'array'
    ];

    public function bankStatement()
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function matchedJournalItem()
    {
        return $this->belongsTo(JournalItem::class, 'matched_with');
    }

    public function scopeUnmatched($query)
    {
        return $query->whereNull('matched_with');
    }

    public function scopeMatched($query)
    {
        return $query->whereNotNull('matched_with');
    }
}