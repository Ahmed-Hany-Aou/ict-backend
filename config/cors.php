<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => env('FRONTEND_URL') === '*' ? ['*'] : array_filter([
        'http://localhost:3000',
        'http://localhost:5173',
        //'https://ict-frontend-production.up.railway.app', // old one 
        'https://hanyedu.up.railway.app',  // new link
        env('FRONTEND_URL'),
    ]),
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true,
];
