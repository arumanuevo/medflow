<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignDispatch extends Model
{
    protected $table = 'campaign_dispatches';

    protected $fillable = [
        'workspace_id',
        'group_id',
        'user_id',
        'total_sensors',
        'enqueued_count',
        'skipped_empty',
        'skipped_suppressed',
        'skipped_invalid',
        'bounces_count',
        'complaints_count',
        'status',
    ];

    public function group()
    {
        return $this->belongsTo(SensorGroup::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
