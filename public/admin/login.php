<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Admin;

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $admin = Admin::where('username', $username)->first();

    if ($admin && password_verify($password, $admin->password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin->id;
        header("Location: /admin/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<?php include '../../includes/header.php'; ?> <!-- ✅ include header -->

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow border-0">
        <div class="card-header text-white text-center" style="background-color: #2E8B57;">
          <h4>Admin Login</h4>
        </div>
        <div class="card-body">
          <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>
          <form method="POST" action="">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" name="username" id="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-success">Login</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?> <!-- ✅ include footer -->
