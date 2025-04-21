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

// Filter setup
$customers = Customer::orderBy('name')->get();
$filter_customer = $_GET['customer_id'] ?? '';
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';

// Query transactions with optional filters
$query = Transaction::with('customer');
if ($filter_customer !== '') {
    $query->where('customer_id', $filter_customer);
}
if ($filter_from !== '') {
    $query->whereDate('created_at', '>=', $filter_from);
}
if ($filter_to !== '') {
    $query->whereDate('created_at', '<=', $filter_to);
}

$transactions = $query->orderBy('customer_id')->orderBy('created_at')->get();
$total_opening = 0;
$total_paid = 0;
?>
<?php include('../../includes/header.php'); ?>
<div class="container mt-4">
<h2 class="text-center text-primary fw-bold mb-4">
  <i class="bi bi-person-plus-fill me-2"></i> TRANSACTIONS HISTORY
</h2>
    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="customer_id" class="form-label">Customer</label>
            <select name="customer_id" id="customer_id" class="form-select">
                <option value="">-- All Customers --</option>
                <?php foreach ($customers as $cust): ?>
                    <option value="<?= $cust->id ?>" <?= ($filter_customer == $cust->id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cust->name) ?> (<?= htmlspecialchars($cust->phone) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="from" class="form-label">From</label>
            <input type="date" name="from" id="from" class="form-control" value="<?= htmlspecialchars($filter_from) ?>">
        </div>
        <div class="col-md-3">
            <label for="to" class="form-label">To</label>
            <input type="date" name="to" id="to" class="form-control" value="<?= htmlspecialchars($filter_to) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="view_transaction.php" class="btn btn-outline-secondary">Reset Filters</a>
        <a href="dashboard.php" class="btn btn-outline-primary">Dashboard</a>
        <a href="add_entry.php" class="btn btn-outline-warning">Add Entry</a>
        <a href="logout.php" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
    </div>

    <?php if ($transactions->isEmpty()): ?>
        <div class="alert alert-warning">No transactions found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Rate (₹)</th>
                        <th>KG</th>
                        <th>Opening (₹)</th>
                        <th>Paid (₹)</th>
                        <th class="text-danger">Due (₹)</th>
                        <th class="text-success">Advance (₹)</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($transactions as $tx):
                    $opening = $tx->rate * $tx->kg;
                    $total_opening += $opening;
                    $total_paid += $tx->paid_amount;
                ?>
                    <tr>
                        <td><?= $tx->id ?></td>
                        <td><?= htmlspecialchars($tx->customer->name ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($tx->customer->phone ?? 'N/A') ?></td>
                        <td>₹<?= number_format($tx->rate, 2) ?></td>
                        <td><?= $tx->kg ?></td>
                        <td>₹<?= number_format($opening, 2) ?></td>
                        <td>₹<?= number_format($tx->paid_amount, 2) ?></td>
                        <td class="text-danger">₹<?= number_format($tx->due_amount, 2) ?></td>
                        <td class="text-success">₹<?= number_format($tx->advance_amount, 2) ?></td>
                        <td><?= date('d-m-Y H:i:s', strtotime($tx->created_at)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $net = $total_paid - $total_opening;
        $final_due = $net < 0 ? abs($net) : 0;
        $final_advance = $net > 0 ? $net : 0;
        ?>
        <div class="alert alert-info mt-4">
            <h5>Summary</h5>
            <p><strong>Total Opening:</strong> ₹<?= number_format($total_opening, 2) ?></p>
            <p><strong>Total Paid:</strong> ₹<?= number_format($total_paid, 2) ?></p>
            <p class="text-danger"><strong>Total Due:</strong> ₹<?= number_format($final_due, 2) ?></p>
            <p class="text-success"><strong>Total Advance:</strong> ₹<?= number_format($final_advance, 2) ?></p>
        </div>

        <a href="export_transactions.php?customer_id=<?= urlencode($filter_customer) ?>&from=<?= urlencode($filter_from) ?>&to=<?= urlencode($filter_to) ?>"
           class="btn btn-success mt-2" target="_blank">
            <i class="bi bi-file-earmark-excel"></i> Download as Excel
        </a>
    <?php endif; ?>
</div>
<?php include('../../includes/footer.php'); ?>
