<?php

return [
    'api_url' => env('FACTUS_API_URL', 'https://api-sandbox.factus.com.co'),
    'client_id' => env('FACTUS_CLIENT_ID'),
    'client_secret' => env('FACTUS_CLIENT_SECRET'),
    'username' => env('FACTUS_USERNAME'),
    'password' => env('FACTUS_PASSWORD'),
    'verify_ssl' => env('FACTUS_VERIFY_SSL', true),
];
