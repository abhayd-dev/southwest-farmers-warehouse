<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Warehouse Configuration
    |--------------------------------------------------------------------------
    */

    // Default category margin percentage
    'default_category_margin' => env('DEFAULT_CATEGORY_MARGIN', 25),

    // Pallet dimensional and weight constraints
    'pallet' => [
        'max_weight_lbs' => env('PALLET_MAX_WEIGHT', 2200),
        'max_height_in' => env('PALLET_MAX_HEIGHT', 60), // standard 5 feet
        'length_in' => env('PALLET_LENGTH', 48), // standard 48
        'width_in' => env('PALLET_WIDTH', 40), // standard 40
    ],

    // Warehouse low stock ratio for rationing (25% of requested)
    'low_stock_ratio' => env('WAREHOUSE_LOW_STOCK_RATIO', 0.25),

    // Retail price rounding (all prices end in .9)
    'retail_rounding' => env('RETAIL_PRICE_ROUNDING', 0.9),

    // Expiration alert schedule (days before expiry)
    'expiration_alerts' => [180, 150, 120, 90, 60, 30],

    // Fast-moving item thresholds
    'fast_moving' => [
        'sales_velocity_threshold' => 10, // units/day
        'stockout_frequency_threshold' => 3, // times per month
    ],

    // Store ordering constraints
    'store_ordering' => [
        'allow_duplicate_in_transit' => false,
        'require_manager_reason_for_changes' => true,
        'auto_generate_po_at_minimum' => true,
    ],
];
