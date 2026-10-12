<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'notes',
        'status',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * User who filed this report.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Target being reported (Project, User, CommunityPost).
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Admin who resolved this report.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for pending reports.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for resolved reports.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for dismissed reports.
     */
    public function scopeDismissed(Builder $query): Builder
    {
        return $query->where('status', 'dismissed');
    }

    /**
     * Human readable label for the reason.
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'spam' => 'Spam / Iklan Berlebihan',
            'illegal_gambling' => 'Konten Ilegal / Judi / Malware',
            'copyright' => 'Plagiasi / Pelanggaran Hak Cipta',
            'harassment' => 'Pelecehan / Ujaran Kebencian',
            'broken_demo' => 'Tautan / Demo Palsu & Merusak',
            default => 'Lainnya',
        };
    }
}
