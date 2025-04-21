

<?php include '../includes/header.php'; ?>

<style>
  body {
    background: url('/assets/images/logo.gif') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #fff;
  }

  /* Preloader */
  #preloader {
    background: #000 url('../assets/images/logo.gif') no-repeat center center;
    background-size: 150px 150px;
    height: 100vh;
    width: 100%;
    position: fixed;
    z-index: 9999;
  }

  .hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    
    padding: 60px;
  }

  .hero-content {
    background: rgba(255, 255, 255, 0.1);
    padding: 50px;
    border-radius: 40px;
    backdrop-filter: blur(6px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
  }

  .hero h1 {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 20px;
    color: #ffc107;
  }

  .hero p {
    font-size: 1.2rem;
    margin-bottom: 20px;
    color: #fff;
  }

  .company-description {
    font-size: 1rem;
    margin-bottom: 30px;
    color: #ddd;
  }

  .btn-custom {
    padding: 10px 25px;
    font-size: 1rem;
    background-color: #ffc107;
    border: none;
    color: #000;
    border-radius: 30px;
    transition: background-color 0.3s ease;
    text-decoration: none;
  }

  .btn-custom:hover {
    background-color: #e0a800;
    color: #fff;
  }
</style>

<!-- Preloader -->
<div id="preloader"></div>

<!-- Home Content -->
<div class="hero">
  <div class="hero-content">
    <h1>Welcome to the Customer Account System</h1>
    <p>Manage your customers, transactions, and balances all in one place.</p>
    <div class="company-description">
      <p><strong>About Us:</strong> Our company Creatornet provides smart and simple account tracking solutions for businesses.<br> From transaction logging to balance summaries, we aim to simplify your daily operations and boost efficiency.</p>
    </div>
    <a href="/admin/dashboard.php" class="btn btn-custom">Go to Dashboard</a>
  </div>
</div>

<script>
// Preloader fade out
window.addEventListener('load', function () {
  const preloader = document.getElementById('preloader');
  preloader.style.display = 'none';
});
</script>

<?php include '../includes/footer.php'; ?>
