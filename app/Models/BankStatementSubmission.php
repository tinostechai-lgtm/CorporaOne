<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankStatementSubmission extends Model
{
    protected $table = 'bank_statement_submissions';
    
    protected $fillable = [
        'account_name',
        'account_number',
        'ifsc_code',
        'bank_name',
        'branch',
        'transactions',
        'extraction_confidence',
        'reconciliation_status',
        'reconciled_at',
        'reconciled_transactions',
        'original_file_name',
        'stored_file_name',
        'created_by'
    ];

    protected $casts = [
        'transactions' => 'array',
        'reconciled_transactions' => 'array',
        'extraction_confidence' => 'float',
        'reconciled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}