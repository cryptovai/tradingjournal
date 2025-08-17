<?php
require_once '../includes/functions.php';
requireAdmin();

$message = '';
$error = '';

// Handle user actions
if ($_POST) {
    $action = $_POST['action'];
    $user_id = intval($_POST['user_id']);
    
    $db = getDB();
    
    if ($action === 'toggle_status') {
        $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND role = 'user'");
        if ($stmt->execute([$user_id])) {
            $message = 'User status updated successfully.';
        } else {
            $error = 'Failed to update user status.';
        }
    } elseif ($action === 'delete_user') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        if ($stmt->execute([$user_id])) {
            $message = 'User deleted successfully.';
        } else {
            $error = 'Failed to delete user.';
        }
    }
}

// Get all users
$db = getDB();
$stmt = $db->query("
    SELECT u.*, 
           COUNT(t.id) as trade_count,
           COALESCE(SUM(t.profit_loss), 0) as total_pnl,
           MAX(t.created_at) as last_trade
    FROM users u 
    LEFT JOIN trades t ON u.id = t.user_id 
    WHERE u.role = 'user'
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Trading Journal Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav class="navbar admin-nav">
        <div class="nav-brand">
            <h1>Trading Journal Admin</h1>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_users.php" class="active">Users</a>
            <a href="manage_trades.php">Trades</a>
            <a href="settings.php">Settings</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
        <div class="user-info">
            Admin: <?php echo htmlspecialchars($_SESSION['email']); ?>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Manage Users</h2>
            <p>View and manage user accounts</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="section">
            <h3>User Accounts</h3>
            
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <p>No user accounts found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Trades</th>
                                <th>Total P&L</th>
                                <th>Last Trade</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($user['trade_count']); ?></td>
                                    <td class="<?php echo $user['total_pnl'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo formatCurrency($user['total_pnl']); ?>
                                    </td>
                                    <td>
                                        <?php echo $user['last_trade'] ? date('M j, Y', strtotime($user['last_trade'])) : 'Never'; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="actions-cell">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $user['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                                <?php echo $user['is_active'] ? 'Disable' : 'Enable'; ?>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this user and all their trades? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- User Statistics -->
        <div class="section">
            <h3>User Statistics</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count(array_filter($users, function($u) { return $u['is_active']; })); ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count(array_filter($users, function($u) { return $u['trade_count'] > 0; })); ?></div>
                    <div class="stat-label">Users with Trades</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo array_sum(array_column($users, 'trade_count')); ?></div>
                    <div class="stat-label">Total Trades</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
