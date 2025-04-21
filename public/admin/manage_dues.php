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

// Fetch all customers
$all_customers = Customer::all();
$due_customers = [];

foreach ($all_customers as $cust) {
    $transactions = Transaction::where('customer_id', $cust->id)->get();
    $final = $transactions->sum('opening_amount');
    $paid = $transactions->sum('paid_amount');
    $due = max($final - $paid, 0);
    $advance = max($paid - $final, 0);

    if ($due > 0) {
        $due_customers[] = [
            'customer' => $cust,
            'final' => $final,
            'paid' => $paid,
            'due' => $due,
            'advance' => $advance
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $payment_amount = floatval($_POST['payment_amount']);

    $customer = Customer::find($customer_id);

    if ($customer) {
        $transactions = Transaction::where('customer_id', $customer_id)->get();
        $final = $transactions->sum('opening_amount');
        $paid = $transactions->sum('paid_amount');

        $current_due = max($final - $paid, 0);
        $current_advance = max($paid - $final, 0);

        // Apply payment
        $new_paid = $paid + $payment_amount;

        $due_after = max($final - $new_paid, 0);
        $advance_after = max($new_paid - $final, 0);

        // Record the payment
        Transaction::create([
            'customer_id' => $customer_id,
            'phone' => $customer->phone,
            'rate' => 0,
            'kg' => 0,
            'opening_amount' => 0,
            'paid_amount' => $payment_amount,
            'due_amount' => $due_after,
            'advance_amount' => $advance_after
        ]);

        // Update customer table after recalculating all transactions
        $transactions = Transaction::where('customer_id', $customer_id)->get();
        $updated_final = $transactions->sum('opening_amount');
        $updated_paid = $transactions->sum('paid_amount');
        $updated_due = max($updated_final - $updated_paid, 0);
        $updated_advance = max($updated_paid - $updated_final, 0);

        $customer->final_amount = $updated_final;
        $customer->due_amount = $updated_due;
        $customer->advance_amount = $updated_advance;
        $customer->save();

        header("Location: manage_dues.php");
        exit();
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-5">
  <h2 class="mb-4 text-center">Manage Customer Dues</h2>

  <?php if (empty($due_customers)): ?>
    <div class="alert alert-success text-center">No customers with due amounts.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>Name</th>
          <th>Phone</th>
          <th class="text-danger">Actual Due</th>
          <th class="text-success">Advance Available</th>
          <th>Make Payment</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($due_customers as $entry): ?>
          <tr>
            <td><?= htmlspecialchars($entry['customer']->name) ?></td>
            <td><?= htmlspecialchars($entry['customer']->phone) ?></td>
            <td class="text-danger fw-bold">₹<?= number_format($entry['due'], 2) ?></td>
            <td class="text-success fw-bold">₹<?= number_format($entry['advance'], 2) ?></td>
            <td>
              <form method="post" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="customer_id" value="<?= $entry['customer']->id ?>">
                <input type="number" name="payment_amount" step="0.01" min="0" class="form-control" placeholder="Enter amount" required>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-cash-coin"></i> Pay
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="text-center">
      <a href="/admin/dashboard.php" class="btn btn-outline-secondary mt-3 px-4">
        <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
      </a>
    </div>
  <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
