<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpContent extends Model
{
    protected $table = 'help_contents';

    protected $fillable = [
        'key',
        'title',
        'content',
        'type',
        'target_page',
        'target_element',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
}