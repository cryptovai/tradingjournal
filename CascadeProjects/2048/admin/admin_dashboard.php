<?php
require_once '../includes/functions.php';
requireAdmin();

// Get admin statistics
$db = getDB();

// Total users
$stmt = $db->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$total_users = $stmt->fetch()['total_users'];

// Total trades
$stmt = $db->query("SELECT COUNT(*) as total_trades FROM trades");
$total_trades = $stmt->fetch()['total_trades'];

// Active users (users with trades in last 30 days)
$stmt = $db->query("SELECT COUNT(DISTINCT user_id) as active_users FROM trades WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$active_users = $stmt->fetch()['active_users'];

// Recent trades
$recent_trades = getAllTrades(10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Trading Journal</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav class="navbar admin-nav">
        <div class="nav-brand">
            <h1>Trading Journal Admin</h1>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php" class="active">Dashboard</a>
            <a href="manage_users.php">Users</a>
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
            <h2>Admin Dashboard</h2>
            <p>System overview and management</p>
        </div>

        <!-- Admin Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_trades; ?></div>
                <div class="stat-label">Total Trades</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $active_users; ?></div>
                <div class="stat-label">Active Users (30d)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_trades > 0 ? round($total_trades / max($total_users, 1), 1) : 0; ?></div>
                <div class="stat-label">Avg Trades/User</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h3>Quick Actions</h3>
            <div class="action-grid">
                <a href="manage_users.php" class="action-card">
                    <h4>Manage Users</h4>
                    <p>View, disable, or delete user accounts</p>
                </a>
                <a href="manage_trades.php" class="action-card">
                    <h4>Manage Trades</h4>
                    <p>View and manage all user trades</p>
                </a>
                <a href="settings.php" class="action-card">
                    <h4>Site Settings</h4>
                    <p>Configure system settings and defaults</p>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="section">
            <h3>Recent Trades</h3>
            
            <?php if (empty($recent_trades)): ?>
                <div class="empty-state">
                    <p>No trades recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="trades-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Date</th>
                                <th>Symbol</th>
                                <th>Entry</th>
                                <th>Exit</th>
                                <th>Quantity</th>
                                <th>P&L</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_trades as $trade): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($trade['email']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($trade['trade_date'])); ?></td>
                                    <td class="symbol"><?php echo htmlspecialchars($trade['symbol']); ?></td>
                                    <td><?php echo formatCurrency($trade['entry_price']); ?></td>
                                    <td><?php echo $trade['exit_price'] ? formatCurrency($trade['exit_price']) : '-'; ?></td>
                                    <td><?php echo number_format($trade['quantity']); ?></td>
                                    <td class="<?php echo $trade['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $trade['profit_loss'] !== null ? formatCurrency($trade['profit_loss']) : '-'; ?>
                                    </td>
                                    <td><?php echo date('M j, H:i', strtotime($trade['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="section-footer">
                    <a href="manage_trades.php" class="btn btn-secondary">View All Trades</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
