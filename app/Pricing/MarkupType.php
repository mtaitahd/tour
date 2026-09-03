<?php

namespace App\Pricing;

/**
 * Markup strategy. PERCENT is a percentage of the complete group cost total.
 * FIXED is an absolute amount charged once per complete group calculation
 * (NOT per person) — the same amount for the 2, 4 and 6 client groups.
 */
enum MarkupType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';
}