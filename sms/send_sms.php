<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/TransactionRecord.php';
require_once __DIR__ . '/../app/Models/Customer.php'; // Include Customer Model
require_once __DIR__ . '/../config/config.php';

use App\Models\TransactionRecord;
use App\Models\Customer;

function send_sms($entry_id) {
    try {
        $entry = TransactionRecord::find($entry_id);

        if (!$entry) {
            throw new Exception("Transaction not found.");
        }

        // Fetch customer details
        $customer = Customer::find($entry->customer_id);
        if (!$customer) {
            throw new Exception("Customer not found.");
        }

        $message = "Payment received: {$entry->paid_amount}. Remaining Due: {$entry->due_amount}.";
        
        // Simulate sending SMS (Replace with actual API)
        file_get_contents("https://sms-api.com/send?api_key=" . SMS_API_KEY . "&to=" . $customer->phone . "&message=" . urlencode($message));

    } catch (Exception $e) {
        error_log("SMS Error: " . $e->getMessage());
    }
}
?>
