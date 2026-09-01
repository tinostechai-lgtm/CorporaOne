<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLines extends Model
{
    protected $fillable = [
        'account_id',
        'date',
        'description',
        'debit',
        'credit',
        'amount',
        'reference',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the account associated with this transaction
     */
    public function account()
    {
        return $this->belongsTo(BankAccount::class, 'account_id');
    }

    /**
     * Get the chart of account associated with this transaction
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Get the creator of the transaction
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}