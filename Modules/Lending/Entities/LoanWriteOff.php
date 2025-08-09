<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanWriteOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'loan_id',
        'write_off_date',
        'write_off_amount',
        'remarks',
        'journal_entry_id',
    ];

    protected $casts = [
        'write_off_date' => 'date',
        'write_off_amount' => 'decimal:2',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(\App\Models\JournalEntry::class);
    }
}
