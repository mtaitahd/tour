<?php

return [
    // Canonical season groupings (months by number). High Season and Low Wet
    // Season are the two supported seasons; Shoulder Season was intentionally
    // removed from the price engine.
    'seasons' => [
        'high' => [1, 2, 3, 6, 7, 8, 9, 10, 12],
        'low_wet' => [4, 5, 11],
    ],

    // Duration buckets used to group packages in the editor. Purely
    // organisational labels — actual durations live on the packages themselves.
    'duration_types' => [
        ['key' => 'short', 'label' => 'Short', 'min_days' => 1, 'max_days' => 3],
        ['key' => 'moderate', 'label' => 'Moderate', 'min_days' => 4, 'max_days' => 7],
        ['key' => 'long', 'label' => 'Long', 'min_days' => 8, 'max_days' => 14],
        ['key' => 'extended', 'label' => 'Extended', 'min_days' => 15, 'max_days' => 999],
    ],

    // How a package is priced. `pp_share` and `pp_private` are the primary
    // per-person bases for the calculator; `full` and `addons` are secondary.
    'charging_bases' => [
        'pp_share' => ['label' => 'Per Person (Sharing)', 'short' => 'PP (Share)'],
        'pp_private' => ['label' => 'Per Person (Private)', 'short' => 'PP (Private)'],
        'full' => ['label' => 'Full Package / Expedition', 'short' => 'Full'],
        'addons' => ['label' => 'Add-on / Optional Extra', 'short' => 'Add-ons'],
    ],

    // Product tiers: the travelling-market tiers map to the package "levels"
    // used by tour categories.
    'category_levels' => [
        'luxury' => ['label' => 'Luxury', 'levels' => ['Luxury', 'Exclusive', 'Elite']],
        'mid_range' => ['label' => 'Mid Range', 'levels' => ['Classic', 'Comfort', 'Premium']],
        'budget' => ['label' => 'Budget', 'levels' => ['Essential', 'Value', 'Plus']],
    ],

    // Cost-line item types recognised when building package prices. These keys
    // are referenced by later phases; labels are for the editor UI.
    'cost_items' => [
        'accommodation' => 'Accommodation',
        'meals' => 'Meals',
        'transport' => 'Transport',
        'fuel' => 'Fuel & Vehicle Running',
        'camping_fees' => 'Camping & Park Fees',
        'entry_fees' => 'Entry Fees',
        'activities' => 'Activities & Experiences',
        'guides' => 'Guides & Crew',
        'flights' => 'Flights',
        'permits' => 'Permits',
        'taxes' => 'Taxes & Levies',
        'margin' => 'Margin / Overheads',
    ],

    // Canonical package-level hierarchy used by the pricing engine. Monetary
    // rates are never defined here. Multi-day categories expose three levels;
    // single-day tours use the STANDARD level only.
    'level_catalog' => [
        'LUXURY' => [
            'label' => 'Luxury',
            'levels' => [
                'LUXURY' => 'Luxury',
                'EXCLUSIVE' => 'Exclusive',
                'ELITE' => 'Elite',
            ],
        ],
        'MID_RANGE' => [
            'label' => 'Mid Range',
            'levels' => [
                'CLASSIC' => 'Classic',
                'COMFORT' => 'Comfort',
                'PREMIUM' => 'Premium',
            ],
        ],
        'BUDGET' => [
            'label' => 'Budget',
            'levels' => [
                'ESSENTIAL' => 'Essential',
                'VALUE' => 'Value',
                'PLUS' => 'Plus',
            ],
        ],
        'SINGLE_DAY' => [
            'label' => 'Single Day',
            'levels' => [
                'STANDARD' => 'Standard Day Trip',
            ],
        ],
    ],

    // Stable month-number to label mapping (1 = January … 12 = December).
    'months' => [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ],

    // Predefined calculator cost items per tour type. These are starting
    // points only — the administrator sets actual rates in the calculator and
    // every row (including predefined ones) has an Include toggle. Government
    // Tax is deliberately NOT a row here: it is a calculator setting.
    // `charging_basis` uses the App\Pricing\ChargingBasis enum values.
    'predefined_costs' => [
        'KILIMANJARO' => [
            ['key' => 'kili_park_fees', 'name' => 'Kilimanjaro National Park Fees', 'charging_basis' => 'PER_PERSON_PER_DAY', 'quantity' => '1', 'taxable' => false, 'notes' => 'Park entry fee charged per client per day.'],
            ['key' => 'kili_camping_fees', 'name' => 'Kilimanjaro Camping Fees', 'charging_basis' => 'PER_PERSON_PER_DAY', 'quantity' => '1', 'taxable' => false, 'notes' => 'Camping fee per client per day.'],
            ['key' => 'kili_hut_fees', 'name' => 'Kilimanjaro Hut Fees', 'charging_basis' => 'PER_PERSON_PER_DAY', 'quantity' => '1', 'taxable' => false, 'notes' => 'Mountain hut fee per client per day.'],
            ['key' => 'rescue_fees', 'name' => 'Rescue Fees', 'charging_basis' => 'PER_PERSON_PER_TRIP', 'quantity' => '1', 'taxable' => false, 'notes' => 'Mandatory rescue / emergency fee, once per client per trip.'],
            ['key' => 'accommodation', 'name' => 'Accommodation', 'charging_basis' => 'PER_ROOM_PER_NIGHT', 'quantity' => '1', 'taxable' => true, 'notes' => 'Per room per night; occupants per room is the room occupancy in the row.'],
            ['key' => 'additional_costs', 'name' => 'Additional Costs', 'charging_basis' => 'PER_GROUP_PER_DAY', 'quantity' => '1', 'taxable' => false, 'notes' => 'Any other group daily costs (full group sharing).'],
        ],
        'SAFARI' => [
            ['key' => 'tarangire_park_fee', 'name' => 'Tarangire National Park Fee', 'charging_basis' => 'PER_PERSON_PER_DAY', 'quantity' => '1', 'taxable' => false, 'notes' => 'Per client per day.'],
            ['key' => 'serengeti_park_fees', 'name' => 'Serengeti National Park Fees', 'charging_basis' => 'PER_PERSON_PER_DAY', 'quantity' => '1', 'taxable' => false, 'notes' => 'Per client per day.'],
            ['key' => 'hotel_concession_fees', 'name' => 'Hotel Concession Fees', 'charging_basis' => 'PER_PERSON_PER_NIGHT', 'quantity' => '1', 'taxable' => false, 'notes' => 'Per client per night.'],
            ['key' => 'camping_fees', 'name' => 'Camping Fees', 'charging_basis' => 'PER_PERSON_PER_NIGHT', 'quantity' => '1', 'taxable' => false, 'notes' => 'Per client per night.'],
            ['key' => 'ngorongoro_transit_fees', 'name' => 'Ngorongoro Transit Fees', 'charging_basis' => 'PER_PERSON_PER_TRIP', 'quantity' => '1', 'taxable' => false, 'notes' => 'Transit fee, once per client per trip.'],
            ['key' => 'ngorongoro_conservation_fees', 'name' => 'Ngorongoro Conservation Fees', 'charging_basis' => 'PER_PERSON_PER_DAY', 'quantity' => '1', 'taxable' => false, 'notes' => 'Per client per day inside the conservation area.'],
            ['key' => 'ngorongoro_crater_fees', 'name' => 'Ngorongoro Crater Fees', 'charging_basis' => 'PER_PERSON_PER_TRIP', 'quantity' => '1', 'taxable' => false, 'notes' => 'Crater descent fee, once per client per trip.'],
            ['key' => 'safari_car_transportation', 'name' => 'Safari Car Transportation', 'charging_basis' => 'PER_VEHICLE_PER_TRIP', 'quantity' => '1', 'taxable' => false, 'notes' => 'Per vehicle per trip; vehicle capacity defaults to 6 and is editable.'],
            ['key' => 'hot_lunch', 'name' => 'Hot Lunch', 'charging_basis' => 'PER_PERSON_PER_TRIP', 'quantity' => '1', 'taxable' => false, 'notes' => 'Once per client per trip.'],
            ['key' => 'lunch_box', 'name' => 'Lunch Box', 'charging_basis' => 'PER_PERSON_PER_TRIP', 'quantity' => '1', 'taxable' => false, 'notes' => 'Once per client per trip.'],
            ['key' => 'accommodation', 'name' => 'Accommodation', 'charging_basis' => 'PER_ROOM_PER_NIGHT', 'quantity' => '1', 'taxable' => true, 'notes' => 'Per room per night; occupants per room is the room occupancy in the row.'],
            ['key' => 'additional_costs', 'name' => 'Additional Costs', 'charging_basis' => 'PER_GROUP_PER_DAY', 'quantity' => '1', 'taxable' => false, 'notes' => 'Any other group daily costs (full group sharing).'],
        ],
    ],
];