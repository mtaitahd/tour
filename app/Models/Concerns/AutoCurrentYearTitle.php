<?php

namespace App\Models\Concerns;

/**
 * Keeps any year mentioned in a model's `title` in sync with the current calendar
 * year. When an admin saves a record whose title contains a 4-digit year (e.g.
 * "Mount Kilimanjaro Safari 2026"), the year is automatically rewritten to the
 * current year (2027, 2028, ...). The title is guaranteed to "read only the
 * current year".
 *
 * Applies on every save (create and update) via the model's `saving` event, and
 * only when the model actually has a `title` attribute.
 */
trait AutoCurrentYearTitle
{
    protected static function bootAutoCurrentYearTitle(): void
    {
        static::saving(function ($model) {
            if (! empty($model->title) && is_string($model->title)) {
                $model->title = static::applyCurrentYearToTitle($model->title);
            }
        });
    }

    /**
     * Replace every 4-digit year (1900-2099) in the string with the current year.
     */
    protected static function applyCurrentYearToTitle(string $title): string
    {
        return preg_replace('/\b(?:19|20)\d{2}\b/', (string) now()->year, $title);
    }
}
