<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESS = 'process';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESS,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'title',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];
}
