<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create(
        '/api/employees',
        'GET',
        ['page' => 1, 'per_page' => 100, 'status' => 'active']
    )
);
echo $response->getContent();
