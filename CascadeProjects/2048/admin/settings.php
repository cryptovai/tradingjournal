<?php
require_once '../includes/functions.php';
requireAdmin();

$message = '';
$error = '';

// Handle settings update
if ($_POST) {
    $settings_to_update = [
        'default_risk_percent',
        'default_fee_rate',
        'site_name'
    ];
    
    $updated_count = 0;
    foreach ($settings_to_update as $key) {
        if (isset($_POST[$key])) {
            $value = sanitizeInput($_POST[$key]);
            if (updateSetting($key, $value)) {
                $updated_count++;
            }
        }
    }
    
    if ($updated_count > 0) {
        $message = 'Settings updated successfully.';
    } else {
        $error = 'Failed to update settings.';
    }
}

// Get current settings
$current_settings = [
    'default_risk_percent' => getSetting('default_risk_percent', '2'),
    'default_fee_rate' => getSetting('default_fee_rate', '0.1'),
    'site_name' => getSetting('site_name', 'Trading Journal Pro')
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Trading Journal Admin</title>
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
            <a href="manage_trades.php">Trades</a>
            <a href="settings.php" class="active">Settings</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
        <div class="user-info">
            Admin: <?php echo htmlspecialchars($_SESSION['email']); ?>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Site Settings</h2>
            <p>Configure system defaults and site information</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="section">
            <h3>System Configuration</h3>
            
            <form method="POST" action="" class="settings-form">
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name" required
                           value="<?php echo htmlspecialchars($current_settings['site_name']); ?>">
                    <small>The name displayed in the site header</small>
                </div>
                
                <div class="form-group">
                    <label for="default_risk_percent">Default Risk Percentage (%)</label>
                    <input type="number" id="default_risk_percent" name="default_risk_percent" 
                           step="0.1" min="0.1" max="100" required
                           value="<?php echo htmlspecialchars($current_settings['default_risk_percent']); ?>">
                    <small>Default risk percentage shown in the position calculator</small>
                </div>
                
                <div class="form-group">
                    <label for="default_fee_rate">Default Fee Rate (%)</label>
                    <input type="number" id="default_fee_rate" name="default_fee_rate" 
                           step="0.01" min="0" max="10" required
                           value="<?php echo htmlspecialchars($current_settings['default_fee_rate']); ?>">
                    <small>Default trading fee rate for calculations</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Settings</button>
            </form>
        </div>

        <!-- System Information -->
        <div class="section">
            <h3>System Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">PHP Version</div>
                    <div class="info-value"><?php echo phpversion(); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Database</div>
                    <div class="info-value">MySQL</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Session Status</div>
                    <div class="info-value"><?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Server Time</div>
                    <div class="info-value"><?php echo date('Y-m-d H:i:s'); ?></div>
                </div>
            </div>
        </div>

        <!-- Database Statistics -->
        <div class="section">
            <h3>Database Statistics</h3>
            <?php
            $db = getDB();
            
            // Get table statistics
            $tables = ['users', 'trades', 'settings'];
            $table_stats = [];
            
            foreach ($tables as $table) {
                $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
                $table_stats[$table] = $stmt->fetch()['count'];
            }
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($table_stats['users']); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($table_stats['trades']); ?></div>
                    <div class="stat-label">Total Trades</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($table_stats['settings']); ?></div>
                    <div class="stat-label">Settings Entries</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
