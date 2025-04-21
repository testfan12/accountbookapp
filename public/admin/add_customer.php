<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Customer;

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Validate input
    if (empty($name) || empty($phone) || empty($address)) {
        $message = "All fields are required.";
    } else {
        // Create a new customer
        $customer = Customer::create([
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
        ]);

        if ($customer) {
            $message = "Customer added successfully!";
        } else {
            $message = "Failed to add customer.";
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-lg p-4">
      <h2 class="text-center text-primary fw-bold mb-4">
  <i class="bi bi-person-plus-fill me-2"></i> ADD CUSTOMER
</h2>
        <?php if (!empty($message)) : ?>
          <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label for="name" class="form-label">Customer Name</label>
            <input type="text" class="form-control" name="name" id="name" required>
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone" id="phone" required>
          </div>

          <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" name="address" id="address" required>
          </div>

          <button type="submit" class="btn btn-success w-100">
            <i class="bi bi-person-plus-fill"></i> Add Customer
          </button>
        </form>

        <a href="dashboard.php" class="btn btn-outline-secondary w-100 mt-3">
          <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
        </a>
      </div>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
