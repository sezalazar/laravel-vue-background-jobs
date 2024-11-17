<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'background_job_id',
        'message',
    ];

    public function backgroundJob(): BelongsTo
    {
        return $this->belongsTo(BackgroundJob::class);
    }
}
