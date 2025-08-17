<?php
// Main entry point for Trading Journal application
require_once 'config/db.php';

// Redirect based on user status
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/admin_dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
} else {
    header('Location: auth/login.php');
}
exit();
?>
