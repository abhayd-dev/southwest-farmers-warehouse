<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Warehouse Configuration
    |--------------------------------------------------------------------------
    */

    // Default category margin percentage
    'default_category_margin' => env('DEFAULT_CATEGORY_MARGIN', 25),

    // Pallet maximum weight in lbs
    'pallet_max_weight' => env('PALLET_MAX_WEIGHT', 2200),

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
