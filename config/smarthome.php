<?php

return [
    'thresholds' => [
        'fire_temperature' => env('SMARTHOME_FIRE_TEMP', 60),
        'humidity'   => env('SMARTHOME_FLOOD_HUMIDITY', 75),
        'light_min'        => env('SMARTHOME_LIGHT_MIN', 350),
        'light_max'        => env('SMARTHOME_LIGHT_MAX', 500),
    ],

    'settings' => [
        'alert_cooldown_minutes'   => 40,
        'light_measurements_count' => 3,
        'chart_data_limit'         => 20,
    ],
];
