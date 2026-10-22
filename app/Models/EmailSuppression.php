<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSuppression extends Model
{
    protected $table = 'email_suppressions';

    protected $fillable = [
        'email',
        'reason',
        'sub_type',
        'workspace_id',
        'source',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
