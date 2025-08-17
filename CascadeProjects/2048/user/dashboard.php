<?php
require_once '../includes/functions.php';
requireLogin();

$user_stats = getUserStats($_SESSION['user_id']);
$recent_trades = getUserTrades($_SESSION['user_id'], 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Trading Journal</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>Trading Journal</h1>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="journal.php">Journal</a>
            <a href="calculator.php">Calculator</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Trading Dashboard</h2>
            <p>Overview of your trading performance</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_stats['total_trades']; ?></div>
                <div class="stat-label">Total Trades</div>
            </div>
            <div class="stat-card <?php echo $user_stats['total_pnl'] >= 0 ? 'positive' : 'negative'; ?>">
                <div class="stat-value"><?php echo formatCurrency($user_stats['total_pnl']); ?></div>
                <div class="stat-label">Total P&L</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $user_stats['winning_trades']; ?></div>
                <div class="stat-label">Winning Trades</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo formatPercent($user_stats['win_rate']); ?></div>
                <div class="stat-label">Win Rate</div>
            </div>
        </div>

        <!-- Recent Trades -->
        <div class="section">
            <div class="section-header">
                <h3>Recent Trades</h3>
                <a href="journal.php" class="btn btn-primary">View All</a>
            </div>
            
            <?php if (empty($recent_trades)): ?>
                <div class="empty-state">
                    <p>No trades recorded yet.</p>
                    <a href="journal.php" class="btn btn-primary">Add Your First Trade</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="trades-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Symbol</th>
                                <th>Entry</th>
                                <th>Exit</th>
                                <th>Quantity</th>
                                <th>P&L</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_trades as $trade): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($trade['trade_date'])); ?></td>
                                    <td class="symbol"><?php echo htmlspecialchars($trade['symbol']); ?></td>
                                    <td><?php echo formatCurrency($trade['entry_price']); ?></td>
                                    <td><?php echo $trade['exit_price'] ? formatCurrency($trade['exit_price']) : '-'; ?></td>
                                    <td><?php echo number_format($trade['quantity']); ?></td>
                                    <td class="<?php echo $trade['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $trade['profit_loss'] !== null ? formatCurrency($trade['profit_loss']) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h3>Quick Actions</h3>
            <div class="action-grid">
                <a href="journal.php" class="action-card">
                    <h4>Add Trade</h4>
                    <p>Record a new trade in your journal</p>
                </a>
                <a href="calculator.php" class="action-card">
                    <h4>Position Calculator</h4>
                    <p>Calculate optimal position size</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
