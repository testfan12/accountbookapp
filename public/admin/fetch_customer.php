<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Customer;

// Prevent accidental output
ob_start();
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');

    if (!empty($phone)) {
        $customer = Customer::where('phone', $phone)->first();
        ob_end_clean(); // Clear any unwanted output
        echo json_encode(['success' => true, 'name' => $customer->name ?? '']);
    } else {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Phone number is required']);
    }
    exit;
}

// Invalid request
ob_end_clean();
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit;
