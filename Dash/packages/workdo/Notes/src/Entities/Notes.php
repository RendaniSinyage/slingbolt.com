<?php

namespace Workdo\Notes\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notes extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'text',
        'color',
        'type',
        'assign_to',
        'workspace_id',
        'created_by',
    ];
}
