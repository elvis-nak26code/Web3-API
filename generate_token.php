<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'test@example.com')->first();
if ($user) {
    $token = $user->createToken('test')->plainTextToken;
    echo $token;
} else {
    echo "User not found";
}
