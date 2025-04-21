<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Transaction;

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request. Transaction ID is required.");
}

$transaction = Transaction::find($_GET['id']);

if (!$transaction) {
    die("Transaction not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction->rate = $_POST['rate'];
    $transaction->pcs = $_POST['pcs'];
    $transaction->paid_amount = $_POST['paid_amount'];
    $transaction->due_amount = $_POST['due_amount'];
    $transaction->advance_amount = $_POST['advance_amount'];
    
    $transaction->save();
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Edit Transaction</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Rate</label>
                <input type="text" name="rate" class="form-control" value="<?= htmlspecialchars($transaction->rate); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Pcs</label>
                <input type="text" name="pcs" class="form-control" value="<?= htmlspecialchars($transaction->pcs); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Paid Amount</label>
                <input type="text" name="paid_amount" class="form-control" value="<?= htmlspecialchars($transaction->paid_amount); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Due Amount</label>
                <input type="text" name="due_amount" class="form-control" value="<?= htmlspecialchars($transaction->due_amount); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Advance Amount</label>
                <input type="text" name="advance_amount" class="form-control" value="<?= htmlspecialchars($transaction->advance_amount); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Transaction</button>
            <a href="admin.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
