<?php
require_once '../includes/functions.php';
requireLogin();

if ($_POST && isset($_POST['trade_id'])) {
    $trade_id = intval($_POST['trade_id']);
    
    if (deleteTrade($trade_id, $_SESSION['user_id'])) {
        header('Location: journal.php?message=Trade deleted successfully');
    } else {
        header('Location: journal.php?error=Failed to delete trade');
    }
} else {
    header('Location: journal.php');
}
exit();
?>
