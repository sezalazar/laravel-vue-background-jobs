<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BackgroundJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'class',
        'method',
        'params',
        'status',
        'retry_count',
        'max_retries',
        'priority',
        'error_message',
        'last_attempted_at',
        'scheduled_at',
        'user_id',
    ];

    protected $casts = [
        'params' => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(JobLog::class);
    }
}
