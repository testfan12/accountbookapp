<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cash Book App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5.3.0 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="/index.php">
      <img src="/assets/images/logo.png" alt="Logo" style="height: 80px;" class="me-2">
      <span class="brand-text">Cash Book App</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link nav-btn" href="/index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-btn" href="/admin/login.php">Login</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Page Content -->
<main class="flex-grow-1">
