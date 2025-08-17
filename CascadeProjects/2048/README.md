# Trading Journal Web Application

A comprehensive web-based trading journal application with user and admin panels, built with PHP and MySQL.

## Features

### User Panel
- **Secure Authentication**: Registration and login with password hashing
- **Trading Journal**: Record trades with symbol, entry/exit prices, quantity, P/L, and notes
- **Position Size Calculator**: Calculate optimal position size based on account size, risk percentage, entry price, and stop loss
- **Dashboard**: Overview of trading performance with statistics and recent trades

### Admin Panel
- **Admin Authentication**: Separate admin login system
- **User Management**: View, disable, and delete user accounts
- **Trade Management**: View and manage all user trades with pagination
- **Site Settings**: Configure default risk percentages, fee rates, and site information
- **System Statistics**: Database and user activity statistics

## Tech Stack

- **Backend**: PHP (procedural with minimal MVC structure)
- **Database**: MySQL with prepared statements
- **Frontend**: HTML5/CSS3 (responsive design, no frameworks)
- **Security**: Password hashing, RBAC, SQL injection prevention

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone/Download** the project files to your web server directory

2. **Database Setup**:
   ```bash
   mysql -u root -p < config/schema.sql
   ```

3. **Configure Database Connection**:
   Edit `config/db.php` and update the database credentials:
   ```php
   private $host = 'localhost';
   private $db_name = 'trading_journal';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

4. **Set Permissions**:
   Ensure the web server has read/write access to the application directory

5. **Access the Application**:
   - Navigate to `http://your-domain.com/` in your browser
   - You'll be redirected to the login page

## Default Credentials

**Admin Account**:
- Email: `admin@tradingjournal.com`
- Password: `admin123`

## Database Schema

### Tables

**users**
- `id` (Primary Key)
- `email` (Unique)
- `password_hash`
- `role` (enum: 'user', 'admin')
- `created_at`
- `is_active`

**trades**
- `id` (Primary Key)
- `user_id` (Foreign Key)
- `symbol`
- `entry_price`
- `exit_price`
- `quantity`
- `profit_loss`
- `notes`
- `trade_date`
- `created_at`
- `updated_at`

**settings**
- `id` (Primary Key)
- `setting_key`
- `setting_value`
- `description`
- `updated_at`

## File Structure

```
trading-journal/
├── config/
│   ├── db.php              # Database connection
│   └── schema.sql          # Database schema
├── auth/
│   ├── register.php        # User registration
│   ├── login.php          # User/Admin login
│   └── logout.php         # Logout functionality
├── user/
│   ├── dashboard.php      # User dashboard
│   ├── journal.php        # Trading journal
│   ├── calculator.php     # Position calculator
│   └── delete_trade.php   # Trade deletion
├── admin/
│   ├── admin_dashboard.php # Admin dashboard
│   ├── manage_users.php   # User management
│   ├── manage_trades.php  # Trade management
│   └── settings.php       # Site settings
├── includes/
│   └── functions.php      # Core functions
├── css/
│   └── styles.css         # Application styles
├── index.php              # Main entry point
└── README.md             # This file
```

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` function
- **Prepared Statements**: All database queries use prepared statements
- **Role-Based Access Control**: Separate user and admin access levels
- **Session Management**: Secure session handling
- **Input Sanitization**: All user inputs are sanitized
- **CSRF Protection**: Form-based protection against cross-site request forgery

## Key Functions

### Authentication
- `isLoggedIn()`: Check if user is authenticated
- `isAdmin()`: Check if user has admin privileges
- `requireLogin()`: Redirect to login if not authenticated
- `requireAdmin()`: Redirect to login if not admin
- `authenticateUser()`: Validate user credentials

### Trading Functions
- `addTrade()`: Add new trade to journal
- `getUserTrades()`: Get user's trades with pagination
- `deleteTrade()`: Delete trade (with permission checks)
- `calculatePositionSize()`: Position size calculator
- `getUserStats()`: Calculate user trading statistics

### Settings Management
- `getSetting()`: Retrieve system setting
- `updateSetting()`: Update system setting

## Usage

### For Users
1. Register a new account or login
2. Access the dashboard to view trading statistics
3. Use the journal to record trades
4. Use the calculator to determine position sizes
5. View trade history and performance metrics

### For Admins
1. Login with admin credentials
2. Access admin dashboard for system overview
3. Manage users (view, disable, delete accounts)
4. Manage trades (view all trades, delete if necessary)
5. Configure site settings and defaults

## Customization

### Adding New Settings
1. Add setting to database via admin panel or SQL
2. Use `getSetting()` and `updateSetting()` functions
3. Add form fields in `admin/settings.php`

### Extending User Features
1. Add new functions to `includes/functions.php`
2. Create new pages in `user/` directory
3. Update navigation in existing files

### Styling Changes
- Modify `css/styles.css` for visual customizations
- The CSS uses CSS Grid and Flexbox for responsive design
- Color scheme can be easily modified by changing CSS variables

## Troubleshooting

### Common Issues

**Database Connection Error**:
- Check database credentials in `config/db.php`
- Ensure MySQL service is running
- Verify database exists and user has proper permissions

**Session Issues**:
- Ensure PHP sessions are enabled
- Check file permissions for session storage
- Verify session configuration in PHP

**Permission Denied**:
- Check file/directory permissions
- Ensure web server can read/write to application directory

### Error Logging
Enable PHP error logging to debug issues:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Contributing

1. Follow existing code style and structure
2. Use prepared statements for all database queries
3. Sanitize all user inputs
4. Test both user and admin functionality
5. Ensure responsive design compatibility

## License

This project is open source and available under the MIT License.
