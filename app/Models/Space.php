<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Space extends Model
{
    public const MIN_FOLLOWERS_TO_CREATE = 100;

    public const MIN_PROJECTS_TO_CREATE = 3;

    public const MIN_REPUTATION_TO_CREATE = 100;

    public const MAX_COHOSTS = 2;

    public const MAX_SPEAKERS = 10;

    public const STAGE_ROLES = ['host', 'cohost', 'speaker'];

    protected $fillable = [
        'host_id',
        'title',
        'slug',
        'type',
        'description',
        'pinned_project_id',
        'status',
        'scheduled_at',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Space $space) {
            if (! $space->slug) {
                $base = Str::slug($space->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $space->slug = $slug;
            }
            if (! $space->started_at && $space->status === 'live') {
                $space->started_at = now();
            }
        });
    }

    /**
     * Evaluate whether a user meets the recognized & professional host requirements.
     *
     * @return array{
     *     eligible: bool,
     *     is_admin: bool,
     *     checks: array{
     *         email_verified: array{passed: bool},
     *         followers: array{current: int, required: int, passed: bool},
     *         projects: array{current: int, required: int, passed: bool},
     *         reputation: array{current: int, required: int, passed: bool},
     *         portfolio: array{passed: bool},
     *         clean_standing: array{passed: bool}
     *     }
     * }
     */
    public static function hostEligibilityFor(?User $user): array
    {
        if (! $user) {
            return [
                'eligible' => false,
                'is_admin' => false,
                'checks' => [
                    'email_verified' => ['passed' => false],
                    'followers' => ['current' => 0, 'required' => self::MIN_FOLLOWERS_TO_CREATE, 'passed' => false],
                    'projects' => ['current' => 0, 'required' => self::MIN_PROJECTS_TO_CREATE, 'passed' => false],
                    'reputation' => ['current' => 0, 'required' => self::MIN_REPUTATION_TO_CREATE, 'passed' => false],
                    'portfolio' => ['passed' => false],
                    'clean_standing' => ['passed' => false],
                ],
            ];
        }

        $followersCount = $user->followers()->count();
        $projectsCount = $user->projects()
            ->where('visibility', 'public')
            ->whereNull('warning_notes')
            ->count();
        $reputation = (int) ($user->reputation_points ?? 0);
        $emailVerified = $user->hasVerifiedEmail();
        $hasPortfolio = filled($user->github_url) || filled($user->website_url);
        $cleanStanding = ! $user->isBanned() && ! $user->hasWarning();

        $checks = [
            'email_verified' => ['passed' => $emailVerified],
            'followers' => [
                'current' => $followersCount,
                'required' => self::MIN_FOLLOWERS_TO_CREATE,
                'passed' => $followersCount >= self::MIN_FOLLOWERS_TO_CREATE,
            ],
            'projects' => [
                'current' => $projectsCount,
                'required' => self::MIN_PROJECTS_TO_CREATE,
                'passed' => $projectsCount >= self::MIN_PROJECTS_TO_CREATE,
            ],
            'reputation' => [
                'current' => $reputation,
                'required' => self::MIN_REPUTATION_TO_CREATE,
                'passed' => $reputation >= self::MIN_REPUTATION_TO_CREATE,
            ],
            'portfolio' => ['passed' => $hasPortfolio],
            'clean_standing' => ['passed' => $cleanStanding],
        ];

        $isAdmin = $user->isAdmin();
        $allPassed = $emailVerified
            && $checks['followers']['passed']
            && $checks['projects']['passed']
            && $checks['reputation']['passed']
            && $hasPortfolio
            && $cleanStanding;

        return [
            'eligible' => ($isAdmin && ! $user->isBanned()) || $allPassed,
            'is_admin' => $isAdmin,
            'checks' => $checks,
        ];
    }

    public static function canBeCreatedBy(?User $user): bool
    {
        return self::hostEligibilityFor($user)['eligible'];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function pinnedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'pinned_project_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SpaceParticipant::class);
    }

    public function signals(): HasMany
    {
        return $this->hasMany(SpaceSignalLog::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'space_participants')
            ->withPivot(['role', 'is_muted', 'hand_raised_at', 'is_forced_muted'])
            ->withTimestamps();
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }
}
