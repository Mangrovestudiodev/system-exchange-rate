<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'referenceId',
        'service_type',
        'currency',
        'buy_rate',
        'sell_rate',
    ];

    // Automatically generate UUID for primary key
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
