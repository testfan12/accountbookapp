<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../sms/send_sms.php';

use App\Models\Transaction;
use App\Models\Customer;

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name']);
    $phone        = trim($_POST['phone']);
    $rate         = floatval($_POST['rate']);
    $kg           = floatval($_POST['kg']);
    $paid_amount  = floatval($_POST['paid_amount']);

    if (empty($phone)) {
        $message = "Phone number is required.";
    } else {
        $opening_amount = $rate * $kg;
        $due_amount     = max(0, $opening_amount - $paid_amount);
        $advance_amount = max(0, $paid_amount - $opening_amount);

        // Find or create customer
        $customer = Customer::firstOrCreate(
            ['phone' => $phone],
            ['name' => $name]
        );

        // Create transaction
        $transaction = Transaction::create([
            'customer_id'    => $customer->id,
            'phone'          => $customer->phone,
            'rate'           => $rate,
            'kg'             => $kg,
            'opening_amount' => $opening_amount,
            'paid_amount'    => $paid_amount,
            'due_amount'     => $due_amount,
            'advance_amount' => $advance_amount,
        ]);

        // Recalculate customer's financial summary
        $totalOpening = Transaction::where('customer_id', $customer->id)->sum('opening_amount');
        $totalPaid    = Transaction::where('customer_id', $customer->id)->sum('paid_amount');
        $net = $totalPaid - $totalOpening;

        $customer->final_amount   = $totalOpening;
        $customer->due_amount     = $net < 0 ? abs($net) : 0;
        $customer->advance_amount = $net > 0 ? abs($net) : 0;
        $customer->save();

        // Send SMS
        // send_sms($transaction->id);

        $message = "Entry added successfully!";
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-lg p-4">
      <h2 class="text-center text-primary fw-bold mb-4">
  <i class="bi bi-person-plus-fill me-2"></i> ADD ENTRY
</h2>

        <?php if (!empty($message)) : ?>
          <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label for="phone" class="form-label">Phone Number *</label>
            <input type="text" class="form-control" name="phone" id="phone" required>
          </div>

          <div class="mb-3">
            <label for="name" class="form-label">Customer Name *</label>
            <input type="text" class="form-control" name="name" id="name" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="rate" class="form-label">Rate</label>
              <input type="number" step="0.01" class="form-control" name="rate" id="rate" required>
            </div>

            <div class="col-md-6 mb-3">
              <label for="kg" class="form-label">Kg</label>
              <input type="number" step="0.01" class="form-control" name="kg" id="kg" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="opening_amount" class="form-label">Opening Amount</label>
            <input type="number" class="form-control" id="opening_amount" readonly>
          </div>

          <div class="mb-3">
            <label for="paid_amount" class="form-label">Paid Amount *</label>
            <input type="number" step="0.01" class="form-control" name="paid_amount" id="paid_amount" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="due_amount" class="form-label">Due Amount</label>
              <input type="number" class="form-control" id="due_amount" readonly>
            </div>

            <div class="col-md-6 mb-3">
              <label for="advance_amount" class="form-label">Advance Amount</label>
              <input type="number" class="form-control" id="advance_amount" readonly>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-plus-circle"></i> Add Entry
          </button>
        </form>

        <a href="dashboard.php" class="btn btn-outline-secondary w-100 mt-3">
          <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const phoneInput = document.getElementById("phone");
  const nameInput = document.getElementById("name");
  const rateInput = document.getElementById("rate");
  const kgInput = document.getElementById("kg");
  const paidInput = document.getElementById("paid_amount");
  const openingInput = document.getElementById("opening_amount");
  const dueInput = document.getElementById("due_amount");
  const advanceInput = document.getElementById("advance_amount");

  function updateAmounts() {
    const rate = parseFloat(rateInput.value) || 0;
    const kg = parseFloat(kgInput.value) || 0;
    const paid = parseFloat(paidInput.value) || 0;

    const opening = rate * kg;
    const due = Math.max(0, opening - paid);
    const advance = Math.max(0, paid - opening);

    openingInput.value = opening.toFixed(2);
    dueInput.value = due.toFixed(2);
    advanceInput.value = advance.toFixed(2);
  }

  [rateInput, kgInput, paidInput].forEach(input =>
    input.addEventListener("input", updateAmounts)
  );

  phoneInput.addEventListener("input", () => {
    const phone = phoneInput.value.trim();
    if (phone.length >= 10) {
      fetch("../../admin/fetch_customer.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `phone=${encodeURIComponent(phone)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.name) {
          nameInput.value = data.name;
        }
      })
      .catch(err => console.error("Fetch error:", err));
    }
  });
});
</script>

<?php include '../../includes/footer.php'; ?>

