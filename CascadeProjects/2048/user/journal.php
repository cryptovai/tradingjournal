<?php
require_once '../includes/functions.php';
requireLogin();

$message = '';
$error = '';

// Handle form submission
if ($_POST) {
    $symbol = sanitizeInput($_POST['symbol']);
    $entry_price = floatval($_POST['entry_price']);
    $exit_price = !empty($_POST['exit_price']) ? floatval($_POST['exit_price']) : null;
    $quantity = intval($_POST['quantity']);
    $notes = sanitizeInput($_POST['notes']);
    $trade_date = $_POST['trade_date'];
    
    if (empty($symbol) || $entry_price <= 0 || $quantity <= 0 || empty($trade_date)) {
        $error = 'Please fill in all required fields with valid values.';
    } else {
        if (addTrade($_SESSION['user_id'], $symbol, $entry_price, $exit_price, $quantity, $notes, $trade_date)) {
            $message = 'Trade added successfully!';
        } else {
            $error = 'Failed to add trade. Please try again.';
        }
    }
}

// Get user's trades
$trades = getUserTrades($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading Journal</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>Trading Journal</h1>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="journal.php" class="active">Journal</a>
            <a href="calculator.php">Calculator</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Trading Journal</h2>
            <p>Record and track your trades</p>
        </div>

        <!-- Add Trade Form -->
        <div class="section">
            <h3>Add New Trade</h3>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="trade-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="symbol">Symbol *</label>
                        <input type="text" id="symbol" name="symbol" required 
                               placeholder="e.g., AAPL" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label for="trade_date">Trade Date *</label>
                        <input type="date" id="trade_date" name="trade_date" required 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="entry_price">Entry Price *</label>
                        <input type="number" id="entry_price" name="entry_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="exit_price">Exit Price</label>
                        <input type="number" id="exit_price" name="exit_price" step="0.01" min="0" 
                               placeholder="Leave empty if still open">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" min="1" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" 
                              placeholder="Trade rationale, strategy, lessons learned..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Trade</button>
            </form>
        </div>

        <!-- Trades List -->
        <div class="section">
            <h3>Your Trades</h3>
            
            <?php if (empty($trades)): ?>
                <div class="empty-state">
                    <p>No trades recorded yet. Add your first trade above!</p>
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
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trades as $trade): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($trade['trade_date'])); ?></td>
                                    <td class="symbol"><?php echo htmlspecialchars($trade['symbol']); ?></td>
                                    <td><?php echo formatCurrency($trade['entry_price']); ?></td>
                                    <td><?php echo $trade['exit_price'] ? formatCurrency($trade['exit_price']) : '<span class="open-position">Open</span>'; ?></td>
                                    <td><?php echo number_format($trade['quantity']); ?></td>
                                    <td class="<?php echo $trade['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $trade['profit_loss'] !== null ? formatCurrency($trade['profit_loss']) : '-'; ?>
                                    </td>
                                    <td class="notes-cell" title="<?php echo htmlspecialchars($trade['notes']); ?>">
                                        <?php echo $trade['notes'] ? substr(htmlspecialchars($trade['notes']), 0, 50) . '...' : '-'; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="delete_trade.php" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this trade?');">
                                            <input type="hidden" name="trade_id" value="<?php echo $trade['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-uppercase symbol input
        document.getElementById('symbol').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
