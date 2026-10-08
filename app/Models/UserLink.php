<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLink extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'url',
        'sort_order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Best-effort domain extraction for display.
     */
    public function domain(): string
    {
        $host = parse_url($this->url, PHP_URL_HOST);

        return $host ? preg_replace('/^www\./', '', $host) : $this->url;
    }
}
