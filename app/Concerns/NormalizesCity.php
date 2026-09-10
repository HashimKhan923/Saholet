<?php

namespace App\Concerns;

use App\Support\CityNormalizer;

/**
 * Normalizes a free-text "city" column whenever the model is saved. Add
 * `protected string $cityColumn = 'shipping_city';` on models whose column isn't
 * literally named `city` (e.g. Order).
 */
trait NormalizesCity
{
    protected static function bootNormalizesCity(): void
    {
        static::saving(function ($model) {
            $column = $model->cityColumn ?? 'city';

            if ($model->{$column} !== null) {
                $model->{$column} = CityNormalizer::normalize($model->{$column});
            }
        });
    }
}
