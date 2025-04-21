<?php
session_start();
session_destroy();
header("Location: /admin/login.php"); // ✅ Redirect to correct path
exit();
