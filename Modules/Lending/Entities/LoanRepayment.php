<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'loan_id',
        'amount_paid',
        'payment_date',
        'remarks',
        'journal_entry_id',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function journalEntry()
    {
        // Assuming JournalEntry model exists in the main app
        return $this->belongsTo(\App\Models\JournalEntry::class);
    }
}
