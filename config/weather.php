<?php

/*
 * Weather card on the dashboard. Data comes from Open-Meteo (https://open-meteo.com),
 * which is free and needs no API key. Defaults point at Butiiti, Kyenjojo.
 * Override any of these in .env if the group's main field is elsewhere.
 */
return [
    'enabled' => (bool) env('WEATHER_ENABLED', true),
    'location' => env('WEATHER_LOCATION', 'Butiiti, Kyenjojo'),
    'latitude' => (float) env('WEATHER_LAT', 0.637),
    'longitude' => (float) env('WEATHER_LON', 30.533),
    'cache_minutes' => (int) env('WEATHER_CACHE_MINUTES', 30),
    'timeout' => (int) env('WEATHER_TIMEOUT', 5),
];
