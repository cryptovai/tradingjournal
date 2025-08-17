<?php
require_once '../config/db.php';

// Authentication and authorization functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../auth/login.php');
        exit();
    }
}

// User management functions
function createUser($email, $password, $role = 'user') {
    $db = getDB();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)");
    return $stmt->execute([$email, $password_hash, $role]);
}

function authenticateUser($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, email, password_hash, role, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: ../auth/login.php');
    exit();
}

// Trade management functions
function addTrade($user_id, $symbol, $entry_price, $exit_price, $quantity, $notes, $trade_date) {
    $db = getDB();
    
    // Calculate profit/loss if exit price is provided
    $profit_loss = null;
    if ($exit_price !== null && $exit_price > 0) {
        $profit_loss = ($exit_price - $entry_price) * $quantity;
    }
    
    $stmt = $db->prepare("INSERT INTO trades (user_id, symbol, entry_price, exit_price, quantity, profit_loss, notes, trade_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $symbol, $entry_price, $exit_price, $quantity, $profit_loss, $notes, $trade_date]);
}

function getUserTrades($user_id, $limit = 50) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM trades WHERE user_id = ? ORDER BY trade_date DESC, created_at DESC LIMIT ?");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

function getAllTrades($limit = 100) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT t.*, u.email 
        FROM trades t 
        JOIN users u ON t.user_id = u.id 
        ORDER BY t.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function deleteTrade($trade_id, $user_id = null) {
    $db = getDB();
    
    if ($user_id !== null) {
        // Regular user can only delete their own trades
        $stmt = $db->prepare("DELETE FROM trades WHERE id = ? AND user_id = ?");
        return $stmt->execute([$trade_id, $user_id]);
    } else {
        // Admin can delete any trade
        $stmt = $db->prepare("DELETE FROM trades WHERE id = ?");
        return $stmt->execute([$trade_id]);
    }
}

// Position size calculator
function calculatePositionSize($account_size, $risk_percent, $entry_price, $stop_loss) {
    if ($entry_price <= 0 || $stop_loss <= 0 || $account_size <= 0 || $risk_percent <= 0) {
        return 0;
    }
    
    $risk_amount = $account_size * ($risk_percent / 100);
    $risk_per_share = abs($entry_price - $stop_loss);
    
    if ($risk_per_share <= 0) {
        return 0;
    }
    
    return floor($risk_amount / $risk_per_share);
}

// Settings management
function getSetting($key, $default = '') {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

function updateSetting($key, $value) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    return $stmt->execute([$value, $key]);
}

// Utility functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function formatPercent($percent) {
    return number_format($percent, 2) . '%';
}

// User statistics
function getUserStats($user_id) {
    $db = getDB();
    
    // Get total trades
    $stmt = $db->prepare("SELECT COUNT(*) as total_trades FROM trades WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_trades = $stmt->fetch()['total_trades'];
    
    // Get total P&L
    $stmt = $db->prepare("SELECT SUM(profit_loss) as total_pnl FROM trades WHERE user_id = ? AND profit_loss IS NOT NULL");
    $stmt->execute([$user_id]);
    $total_pnl = $stmt->fetch()['total_pnl'] ?? 0;
    
    // Get winning trades
    $stmt = $db->prepare("SELECT COUNT(*) as winning_trades FROM trades WHERE user_id = ? AND profit_loss > 0");
    $stmt->execute([$user_id]);
    $winning_trades = $stmt->fetch()['winning_trades'];
    
    $win_rate = $total_trades > 0 ? ($winning_trades / $total_trades) * 100 : 0;
    
    return [
        'total_trades' => $total_trades,
        'total_pnl' => $total_pnl,
        'winning_trades' => $winning_trades,
        'win_rate' => $win_rate
    ];
}
?>
