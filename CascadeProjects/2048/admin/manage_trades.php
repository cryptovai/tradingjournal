<?php
require_once '../includes/functions.php';
requireAdmin();

$message = '';
$error = '';

// Handle trade deletion
if ($_POST && isset($_POST['delete_trade'])) {
    $trade_id = intval($_POST['trade_id']);
    
    if (deleteTrade($trade_id)) {
        $message = 'Trade deleted successfully.';
    } else {
        $error = 'Failed to delete trade.';
    }
}

// Get all trades with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

$db = getDB();

// Get total count
$stmt = $db->query("SELECT COUNT(*) as total FROM trades");
$total_trades = $stmt->fetch()['total'];
$total_pages = ceil($total_trades / $per_page);

// Get trades for current page
$stmt = $db->prepare("
    SELECT t.*, u.email 
    FROM trades t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$per_page, $offset]);
$trades = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trades - Trading Journal Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav class="navbar admin-nav">
        <div class="nav-brand">
            <h1>Trading Journal Admin</h1>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_users.php">Users</a>
            <a href="manage_trades.php" class="active">Trades</a>
            <a href="settings.php">Settings</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
        <div class="user-info">
            Admin: <?php echo htmlspecialchars($_SESSION['email']); ?>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Manage Trades</h2>
            <p>View and manage all user trades</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header">
                <h3>All Trades (<?php echo number_format($total_trades); ?> total)</h3>
                <div class="pagination-info">
                    Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                </div>
            </div>
            
            <?php if (empty($trades)): ?>
                <div class="empty-state">
                    <p>No trades found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="trades-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Symbol</th>
                                <th>Entry</th>
                                <th>Exit</th>
                                <th>Quantity</th>
                                <th>P&L</th>
                                <th>Notes</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trades as $trade): ?>
                                <tr>
                                    <td><?php echo $trade['id']; ?></td>
                                    <td><?php echo htmlspecialchars($trade['email']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($trade['trade_date'])); ?></td>
                                    <td class="symbol"><?php echo htmlspecialchars($trade['symbol']); ?></td>
                                    <td><?php echo formatCurrency($trade['entry_price']); ?></td>
                                    <td><?php echo $trade['exit_price'] ? formatCurrency($trade['exit_price']) : '<span class="open-position">Open</span>'; ?></td>
                                    <td><?php echo number_format($trade['quantity']); ?></td>
                                    <td class="<?php echo $trade['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $trade['profit_loss'] !== null ? formatCurrency($trade['profit_loss']) : '-'; ?>
                                    </td>
                                    <td class="notes-cell" title="<?php echo htmlspecialchars($trade['notes']); ?>">
                                        <?php echo $trade['notes'] ? substr(htmlspecialchars($trade['notes']), 0, 30) . '...' : '-'; ?>
                                    </td>
                                    <td><?php echo date('M j, H:i', strtotime($trade['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this trade?');">
                                            <input type="hidden" name="delete_trade" value="1">
                                            <input type="hidden" name="trade_id" value="<?php echo $trade['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Trade Statistics -->
        <div class="section">
            <h3>Trade Statistics</h3>
            <?php
            // Calculate statistics
            $stmt = $db->query("SELECT 
                COUNT(*) as total_trades,
                COUNT(CASE WHEN profit_loss > 0 THEN 1 END) as winning_trades,
                COUNT(CASE WHEN profit_loss < 0 THEN 1 END) as losing_trades,
                COUNT(CASE WHEN profit_loss IS NULL THEN 1 END) as open_trades,
                COALESCE(SUM(profit_loss), 0) as total_pnl,
                COALESCE(AVG(profit_loss), 0) as avg_pnl
            FROM trades WHERE profit_loss IS NOT NULL");
            $stats = $stmt->fetch();
            $win_rate = $stats['total_trades'] > 0 ? ($stats['winning_trades'] / $stats['total_trades']) * 100 : 0;
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['total_trades']); ?></div>
                    <div class="stat-label">Total Trades</div>
                </div>
                <div class="stat-card positive">
                    <div class="stat-value"><?php echo number_format($stats['winning_trades']); ?></div>
                    <div class="stat-label">Winning Trades</div>
                </div>
                <div class="stat-card negative">
                    <div class="stat-value"><?php echo number_format($stats['losing_trades']); ?></div>
                    <div class="stat-label">Losing Trades</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['open_trades']); ?></div>
                    <div class="stat-label">Open Positions</div>
                </div>
                <div class="stat-card <?php echo $stats['total_pnl'] >= 0 ? 'positive' : 'negative'; ?>">
                    <div class="stat-value"><?php echo formatCurrency($stats['total_pnl']); ?></div>
                    <div class="stat-label">Total P&L</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo formatPercent($win_rate); ?></div>
                    <div class="stat-label">Win Rate</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
