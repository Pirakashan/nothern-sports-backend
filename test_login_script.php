<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = \App\Models\User::where('email', 'vendor1@roamio.com')->first();
$password = $user->password;

$request = Illuminate\Http\Request::create(
    '/api/login',
    'POST',
    ['email' => 'vendor1@roamio.com', 'password' => $password]
);
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Response: " . $response->getContent() . "\n";
