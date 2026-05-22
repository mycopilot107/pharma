<?php

return [
    /*
    | GPS pings & geofence events are stored in MongoDB (connection: mongodb).
    | MySQL keeps users, visits, attendance, customers.
    */
    'mongodb_connection' => env('TRACKING_MONGODB_CONNECTION', 'mongodb'),

    'ping_interval_seconds' => (int) env('TRACKING_PING_INTERVAL', 30),
    'background_ping_interval_seconds' => (int) env('TRACKING_BACKGROUND_PING_INTERVAL', 60),
    'live_stale_minutes' => (int) env('TRACKING_LIVE_STALE_MINUTES', 5),
    'geofence_default_radius_meters' => (int) env('TRACKING_GEOFENCE_RADIUS', 150),
    'dwell_stop_minutes' => (int) env('TRACKING_DWELL_STOP_MINUTES', 5),
    'visit_min_duration_minutes' => (int) env('TRACKING_VISIT_MIN_DURATION', 3),
    'visit_max_distance_meters' => (int) env('TRACKING_VISIT_MAX_DISTANCE', 500),
    'fraud_repeat_location_meters' => (int) env('TRACKING_FRAUD_REPEAT_METERS', 50),
];
