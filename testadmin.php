<?php
// Example when creating a new admin user

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Database\Capsule\Manager as Capsule;

$username = 'admin';
$password = 'admin123';

// Hash the password before storing it in the database
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert the admin with hashed password
Capsule::table('admins')->insert([
    'username' => $username,
    'password' => $hashedPassword,
]);

echo "Admin created successfully!";
?>
