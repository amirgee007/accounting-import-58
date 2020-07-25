<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncJob extends Model
{
    protected $table = 'sync_jobs';
    protected $guarded = [];

    public function scopeActiveStatus($query, $type)
    {
        return $query->whereIn('status', ['active' ,'pending'])->where('type', $type)->orderByDesc('id');
    }
}
