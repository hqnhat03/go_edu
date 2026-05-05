<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://' . env('FRONTEND_DOMAIN', 'hqnhat.id.vn'),
        'https://' . env('FRONTEND_DOMAIN', 'hqnhat.id.vn'),
        'http://teacher.' . env('FRONTEND_DOMAIN', 'hqnhat.id.vn'),
        'https://teacher.' . env('FRONTEND_DOMAIN', 'hqnhat.id.vn'),
        'http://student.' . env('FRONTEND_DOMAIN', 'hqnhat.id.vn'),
        'https://student.' . env('FRONTEND_DOMAIN', 'hqnhat.id.vn'),
        'http://localhost:3000', // Cho development (Next.js/Vite)
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
