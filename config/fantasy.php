<?php

return [
    // Base points for a rostered driver's finishing position.
    'driver_position_points' => [
        1 => 25, 2 => 18, 3 => 15, 4 => 12, 5 => 10,
        6 => 8, 7 => 6, 8 => 4, 9 => 2, 10 => 1,
    ],

    'bonus' => [
        'pole_position' => 5, // starting the race from grid position 1
        'fastest_lap' => 3,
        'podium' => 5, // finishing in the top 3
    ],

    'penalty' => [
        'dnf' => -5,
    ],

    // Multiplier applied to the rostered constructor's real race points
    // (the sum of its two drivers' real points in that race).
    'constructor_points_multiplier' => 1.0,

    // Duration of each asynchronous sealed-bid auction round, before automatic resolution.
    'auction_round_duration_hours' => 48,

    // Default points for a correct prediction, per type. Each league can
    // override these values at creation (League::predictionPoints()).
    // To add a new prediction type in the future: a row here + a case in
    // FantasyScoreCalculator::scorePredictions().
    'prediction_points' => [
        'pole' => 5,
        'fastest_pit_stop' => 5,
        'dnf' => 5,
    ],
];
