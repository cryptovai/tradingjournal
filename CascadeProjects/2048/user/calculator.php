<?php
require_once '../includes/functions.php';
requireLogin();

$result = null;
$error = '';

// Get default settings
$default_risk = getSetting('default_risk_percent', '2');

if ($_POST) {
    $account_size = floatval($_POST['account_size']);
    $risk_percent = floatval($_POST['risk_percent']);
    $entry_price = floatval($_POST['entry_price']);
    $stop_loss = floatval($_POST['stop_loss']);
    
    if ($account_size <= 0 || $risk_percent <= 0 || $entry_price <= 0 || $stop_loss <= 0) {
        $error = 'All fields must be positive numbers.';
    } elseif ($entry_price == $stop_loss) {
        $error = 'Entry price and stop loss cannot be the same.';
    } else {
        $position_size = calculatePositionSize($account_size, $risk_percent, $entry_price, $stop_loss);
        $risk_amount = $account_size * ($risk_percent / 100);
        $risk_per_share = abs($entry_price - $stop_loss);
        $total_cost = $position_size * $entry_price;
        
        $result = [
            'position_size' => $position_size,
            'risk_amount' => $risk_amount,
            'risk_per_share' => $risk_per_share,
            'total_cost' => $total_cost,
            'account_percentage' => ($total_cost / $account_size) * 100
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Position Calculator - Trading Journal</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1>Trading Journal</h1>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="journal.php">Journal</a>
            <a href="calculator.php" class="active">Calculator</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Position Size Calculator</h2>
            <p>Calculate optimal position size based on risk management</p>
        </div>

        <div class="calculator-container">
            <div class="calculator-form">
                <h3>Calculate Position Size</h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="account_size">Account Size ($) *</label>
                        <input type="number" id="account_size" name="account_size" step="0.01" min="0" required
                               value="<?php echo isset($_POST['account_size']) ? $_POST['account_size'] : ''; ?>"
                               placeholder="e.g., 10000">
                    </div>
                    
                    <div class="form-group">
                        <label for="risk_percent">Risk Percentage (%) *</label>
                        <input type="number" id="risk_percent" name="risk_percent" step="0.1" min="0.1" max="100" required
                               value="<?php echo isset($_POST['risk_percent']) ? $_POST['risk_percent'] : $default_risk; ?>"
                               placeholder="e.g., 2">
                        <small>Recommended: 1-3% per trade</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="entry_price">Entry Price ($) *</label>
                        <input type="number" id="entry_price" name="entry_price" step="0.01" min="0" required
                               value="<?php echo isset($_POST['entry_price']) ? $_POST['entry_price'] : ''; ?>"
                               placeholder="e.g., 150.50">
                    </div>
                    
                    <div class="form-group">
                        <label for="stop_loss">Stop Loss ($) *</label>
                        <input type="number" id="stop_loss" name="stop_loss" step="0.01" min="0" required
                               value="<?php echo isset($_POST['stop_loss']) ? $_POST['stop_loss'] : ''; ?>"
                               placeholder="e.g., 145.00">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Calculate Position Size</button>
                </form>
            </div>
            
            <?php if ($result): ?>
                <div class="calculator-results">
                    <h3>Calculation Results</h3>
                    
                    <div class="result-grid">
                        <div class="result-item">
                            <div class="result-label">Recommended Position Size</div>
                            <div class="result-value primary"><?php echo number_format($result['position_size']); ?> shares</div>
                        </div>
                        
                        <div class="result-item">
                            <div class="result-label">Risk Amount</div>
                            <div class="result-value"><?php echo formatCurrency($result['risk_amount']); ?></div>
                        </div>
                        
                        <div class="result-item">
                            <div class="result-label">Risk Per Share</div>
                            <div class="result-value"><?php echo formatCurrency($result['risk_per_share']); ?></div>
                        </div>
                        
                        <div class="result-item">
                            <div class="result-label">Total Cost</div>
                            <div class="result-value"><?php echo formatCurrency($result['total_cost']); ?></div>
                        </div>
                        
                        <div class="result-item">
                            <div class="result-label">Account Percentage</div>
                            <div class="result-value"><?php echo formatPercent($result['account_percentage']); ?></div>
                        </div>
                    </div>
                    
                    <div class="calculation-breakdown">
                        <h4>How this was calculated:</h4>
                        <ol>
                            <li>Risk Amount = Account Size × Risk Percentage = <?php echo formatCurrency($_POST['account_size']); ?> × <?php echo $_POST['risk_percent']; ?>% = <?php echo formatCurrency($result['risk_amount']); ?></li>
                            <li>Risk Per Share = |Entry Price - Stop Loss| = |<?php echo formatCurrency($_POST['entry_price']); ?> - <?php echo formatCurrency($_POST['stop_loss']); ?>| = <?php echo formatCurrency($result['risk_per_share']); ?></li>
                            <li>Position Size = Risk Amount ÷ Risk Per Share = <?php echo formatCurrency($result['risk_amount']); ?> ÷ <?php echo formatCurrency($result['risk_per_share']); ?> = <?php echo number_format($result['position_size']); ?> shares</li>
                        </ol>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h3>Risk Management Tips</h3>
            <div class="tips-grid">
                <div class="tip-card">
                    <h4>1-3% Rule</h4>
                    <p>Never risk more than 1-3% of your account on a single trade to preserve capital.</p>
                </div>
                <div class="tip-card">
                    <h4>Stop Loss</h4>
                    <p>Always set a stop loss before entering a trade to limit potential losses.</p>
                </div>
                <div class="tip-card">
                    <h4>Position Sizing</h4>
                    <p>Adjust position size based on the distance to your stop loss, not just gut feeling.</p>
                </div>
                <div class="tip-card">
                    <h4>Diversification</h4>
                    <p>Don't put all your capital into one position, spread risk across multiple trades.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time calculation preview
        function updatePreview() {
            const accountSize = parseFloat(document.getElementById('account_size').value) || 0;
            const riskPercent = parseFloat(document.getElementById('risk_percent').value) || 0;
            const entryPrice = parseFloat(document.getElementById('entry_price').value) || 0;
            const stopLoss = parseFloat(document.getElementById('stop_loss').value) || 0;
            
            if (accountSize > 0 && riskPercent > 0 && entryPrice > 0 && stopLoss > 0 && entryPrice !== stopLoss) {
                const riskAmount = accountSize * (riskPercent / 100);
                const riskPerShare = Math.abs(entryPrice - stopLoss);
                const positionSize = Math.floor(riskAmount / riskPerShare);
                
                // You could add a preview section here if desired
            }
        }
        
        // Add event listeners for real-time updates
        ['account_size', 'risk_percent', 'entry_price', 'stop_loss'].forEach(id => {
            document.getElementById(id).addEventListener('input', updatePreview);
        });
    </script>
</body>
</html>
