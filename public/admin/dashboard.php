<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Transaction;
use App\Models\Customer;

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$customers = Customer::all();
$transactions = Transaction::with('customer')->get();

$transactions = $transactions->sortBy([
    fn($a, $b) => $a->customer_id <=> $b->customer_id,
    fn($a, $b) => strtotime($a->created_at) <=> strtotime($b->created_at)
]);

$total_opening = 0;
$total_paid = 0;
$total_due = 0;
$total_advance = 0;

$customer_balances = [];
$transaction_rows = [];

foreach ($transactions as $transaction) {
    $customer = $transaction->customer;
    $cid = $transaction->customer_id;

    $rate = $transaction->rate;
    $kg = $transaction->kg;
    $opening = $rate * $kg;
    $paid = $transaction->paid_amount;

    $total_opening += $opening;
    $total_paid += $paid;

    if (!isset($customer_balances[$cid])) {
        $customer_balances[$cid] = [
            'name' => $customer ? htmlspecialchars($customer->name) : 'Unknown',
            'advance' => 0,
            'due' => 0
        ];
    }

    $adjusted_opening = $opening;

    // Apply advance to this transaction
    if ($customer_balances[$cid]['advance'] > 0) {
        $applied = min($adjusted_opening, $customer_balances[$cid]['advance']);
        $adjusted_opening -= $applied;
        $customer_balances[$cid]['advance'] -= $applied;
    }

    if ($paid >= $adjusted_opening) {
        $advance = $paid - $adjusted_opening;
        $customer_balances[$cid]['advance'] += $advance;
        $transaction_due = 0;
        $transaction_advance = $advance;
    } else {
        $due = $adjusted_opening - $paid;
        $customer_balances[$cid]['due'] += $due;
        $transaction_due = $due;
        $transaction_advance = 0;
    }

    $transaction_rows[] = [
        'id' => $transaction->id,
        'name' => $customer_balances[$cid]['name'],
        'phone' => $customer ? htmlspecialchars($customer->phone) : 'N/A',
        'rate' => $rate,
        'kg' => $kg,
        'opening' => $opening,
        'paid' => $paid,
        'due' => $transaction_due,
        'advance' => $transaction_advance,
        'created_at' => $transaction->created_at,
    ];

    // Update customers table
    $customer->final_amount = $transactions->where('customer_id', $cid)->sum(function ($t) {
        return $t->rate * $t->kg;
    });
    $customer->due_amount = $customer_balances[$cid]['due'];
    $customer->advance_amount = $customer_balances[$cid]['advance'];
    $customer->save();
}

// Final net due/advance logic
$net_due = 0;
$net_advance = 0;

foreach ($customer_balances as $balance) {
    if ($balance['due'] > $balance['advance']) {
        $net_due += $balance['due'] - $balance['advance'];
    } elseif ($balance['advance'] > $balance['due']) {
        $net_advance += $balance['advance'] - $balance['due'];
    }
}
?>

<?php
include '../../includes/header.php';
?>

<div class="container mt-5">
<h2 class="text-center text-primary fw-bold mb-4">
  <i class="bi bi-person-plus-fill me-2"></i> ADMIN DASHBOARD
</h2>

  <div class="d-flex flex-wrap gap-2 mb-4">
    <a class="btn btn-primary" href="add_customer.php">➕ Create Customer</a>
    <a class="btn btn-success" href="view_transaction.php">👥 View Customers</a>
    <a class="btn btn-info" href="manage_dues.php">💳 Manage Dues</a>
    <a class="btn btn-warning" href="add_entry.php">💰 Add Transaction</a>
    <a class="btn btn-danger" href="logout.php" onclick="return confirm('Are you sure you want to log out?');">🚪 Logout</a>
  </div>

  <h3>Recent Transactions</h3>
  <?php if (empty($transaction_rows)): ?>
    <p class="alert alert-warning">No transactions found.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
          <th>Transaction ID</th>
          <th>Customer Name</th>
          <th>Phone</th>
          <th>Rate</th>
          <th>KG</th>
          <th>Opening</th>
          <th>Paid</th>
          <th class="text-danger">Due</th>
          <th class="text-success">Advance</th>
          <th>Date</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($transaction_rows as $row): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td>₹<?= number_format($row['rate'], 2) ?></td>
            <td><?= $row['kg'] ?></td>
            <td>₹<?= number_format($row['opening'], 2) ?></td>
            <td>₹<?= number_format($row['paid'], 2) ?></td>
            <td class="text-danger">₹<?= number_format($row['due'], 2) ?></td>
            <td class="text-success">₹<?= number_format($row['advance'], 2) ?></td>
            <td><?= date('d-m-Y H:i:s', strtotime($row['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="alert alert-info mt-4">
      <h4>Transaction Summary</h4>
      <p><strong>Total Opening Amount:</strong> ₹<?= number_format($total_opening, 2); ?></p>
      <p><strong>Total Paid Amount:</strong> ₹<?= number_format($total_paid, 2); ?></p>
      <p class="text-danger"><strong>Current Due:</strong> ₹<?= number_format($net_due, 2); ?></p>
      <p class="text-success"><strong>Current Advance:</strong> ₹<?= number_format($net_advance, 2); ?></p>
    </div>
  <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
