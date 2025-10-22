<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class ApiToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'is_active',
        'expires_at',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            // Hash the token before storing in DB
            $model->token = Hash::make($model->token);
        });
    }

    /**
     * Validate a plain bearer token.
     *
     * @param  string  $plainToken
     * @return bool
     */
    public static function validateToken(string $plainToken): bool
    {
        // Get all active, non-expired tokens
        $tokens = self::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
            })->get();

        foreach ($tokens as $token) {
            if (Hash::check($plainToken, $token->token)) {
                return true; // Valid token found
            }
        }

        return false;
    }
}
