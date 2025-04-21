<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Customer;
use App\Models\Transaction;

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Handle Deletion
if (isset($_GET['delete_customer'])) {
    $customer = Customer::find($_GET['delete_customer']);
    if ($customer) {
        $customer->transactions()->delete(); // Delete associated transactions
        $customer->delete();
        header("Location: admin.php");
        exit();
    }
}

if (isset($_GET['delete_transaction'])) {
    $transaction = Transaction::find($_GET['delete_transaction']);
    if ($transaction) {
        $transaction->delete();
        header("Location: admin.php");
        exit();
    }
}

// Fetch Data
$customers = Customer::all();
$transactions = Transaction::with('customer')->get();

// Search by ID
$search_customer_id = $_GET['search_customer_id'] ?? null;
$search_transaction_id = $_GET['search_transaction_id'] ?? null;

if ($search_customer_id) {
    $customers = Customer::where('id', $search_customer_id)->get();
}

if ($search_transaction_id) {
    $transactions = Transaction::with('customer')->where('id', $search_transaction_id)->get();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Admin Panel</h2>
        
        <h3>Customers</h3>
        <form method="GET" class="mb-3">
            <input type="text" name="search_customer_id" placeholder="Search by Customer ID" class="form-control w-25 d-inline-block">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?= $customer->id; ?></td>
                    <td><?= htmlspecialchars($customer->name); ?></td>
                    <td><?= htmlspecialchars($customer->phone); ?></td>
                    <td><?= htmlspecialchars($customer->address); ?></td>
                    <td>
                        <a href="edit_customer.php?id=<?= $customer->id; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="admin.php?delete_customer=<?= $customer->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h3>Transactions</h3>
        <form method="GET" class="mb-3">
            <input type="text" name="search_transaction_id" placeholder="Search by Transaction ID" class="form-control w-25 d-inline-block">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Rate</th>
                    <th>Pcs</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Advance Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= $transaction->id; ?></td>
                    <td><?= $transaction->customer ? htmlspecialchars($transaction->customer->name) : 'Unknown'; ?></td>
                    <td>₹<?= number_format($transaction->rate, 2); ?></td>
                    <td><?= $transaction->pcs; ?></td>
                    <td>₹<?= number_format($transaction->paid_amount, 2); ?></td>
                    <td>₹<?= number_format(max(0, ($transaction->rate * $transaction->pcs) - $transaction->paid_amount), 2); ?></td>
                    <td>₹<?= number_format(max(0, $transaction->paid_amount - ($transaction->rate * $transaction->pcs)), 2); ?></td>
                    <td>
                        <a href="edit_transaction.php?id=<?= $transaction->id; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="admin.php?delete_transaction=<?= $transaction->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
