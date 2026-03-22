<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'phone',
    'email',
    'guests_count',
    'attendance',
    'message',
    'status',
    'table_number',
])]
class Rsvp extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /** Maximum number of approved RSVPs (each gets a table number 1–this value). */
    public const APPROVED_CAPACITY = 100;

    public static function approvedCount(): int
    {
        return static::query()->where('status', self::STATUS_APPROVED)->count();
    }

    public static function isFullyBooked(): bool
    {
        return static::approvedCount() >= self::APPROVED_CAPACITY;
    }

    protected static function booted(): void
    {
        static::creating(function (Rsvp $rsvp): void {
            if ($rsvp->status === null || $rsvp->status === '') {
                $rsvp->status = self::STATUS_PENDING;
            }
        });
    }
}
