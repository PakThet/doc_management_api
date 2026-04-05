<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = \App\Models\User::where('email', 'superadmin@system.com')->first();
$user->password = \Illuminate\Support\Facades\Hash::make('password');
$user->save();
echo "Password reset to 'password'\n";
