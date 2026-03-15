<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'vendor1@roamio.com')->first();
echo "User found: " . ($user ? 'YES' : 'NO') . "\n";
echo "ID: " . $user->id . "\n";
echo "Role: " . $user->role . "\n";
echo "District ID: " . ($user->district_id ?? 'NULL') . "\n";
echo "Password: " . $user->password . "\n";
echo "Password starts with \$2y\$: " . (str_starts_with($user->password, '$2y$') ? 'YES' : 'NO') . "\n";

// Test loading district
try {
    $loaded = $user->load('district');
    echo "District load: SUCCESS\n";
    echo "District: " . json_encode($loaded->district) . "\n";
} catch (\Exception $e) {
    echo "District load ERROR: " . $e->getMessage() . "\n";
}

// Test token creation
try {
    $token = $user->createToken('test-token')->plainTextToken;
    echo "Token creation: SUCCESS\n";
} catch (\Exception $e) {
    echo "Token creation ERROR: " . $e->getMessage() . "\n";
}
