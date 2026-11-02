<?php

namespace App\Support;

class LocalSpecialistCopy
{
    public static function replace(string $text): string
    {
        $parts = preg_split('/(<[^>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as &$part) {
            if (str_starts_with($part, '<')) continue;
            $part = preg_replace_callback('/\b(?:local\s+)?(?:safari\s+|travel\s+)?(experts?)\b/i', function ($match) {
                $label = strtolower($match[1]) === 'experts' ? 'local specialists' : 'local specialist';
                return ctype_upper($match[0][0]) ? ucwords($label) : $label;
            }, $part);
        }

        return implode('', $parts);
    }
}
