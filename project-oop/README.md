# Truck Management System

A comprehensive truck fleet management system built with PHP, HTML5, and CSS. Features a modern dark purple theme and is designed to work seamlessly with Laragon.

## Features

### 🚚 Core Functionality
- **Dashboard**: Overview statistics and recent activity
- **Truck Management**: Add, edit, and track truck status
- **User Management**: Manage drivers and staff
- **Warehouse Overview**: Monitor capacity and locations
- **Assignment System**: Assign and return trucks with logging
- **Activity Logs**: Comprehensive reporting and filtering

### 🎨 Design
- Modern dark theme with purple accents
- Responsive design for desktop and mobile
- Clean card-based layout
- Smooth animations and transitions
- Professional status indicators

## Setup Instructions

### 1. Database Setup
1. Open phpMyAdmin in Laragon
2. Create a new database called `truck_management`
3. Import the `database.sql` file or run the SQL commands manually

### 2. Configuration
- The system is pre-configured for Laragon's default settings
- Database credentials are in `config.php` (localhost, root, no password)
- Modify `config.php` if your setup differs

### 3. Login Credentials
**Demo Admin Accounts:**
- Username: `admin` | Password: `password123`
- Username: `manager` | Password: `manager123`

## File Structure

```
truck-management/
├── index.html          # Welcome page
├── login.php           # Admin login
├── index.php           # Dashboard
├── trucks.php          # Truck management
├── users.php           # User management
├── warehouse.php       # Warehouse overview
├── assignments.php     # Assignment & returns
├── logs.php           # Activity logs & reports
├── logout.php         # Logout handler
├── config.php         # Database configuration
├── style.css          # Styling and theme
├── database.sql       # Database schema and sample data
└── README.md          # This file
```

## Usage

### Dashboard
- View total trucks, in-use, and warehouse statistics
- Quick access to main functions
- Recent activity feed

### Truck Management
- Add new trucks with ID, model, and type
- View all trucks with current status
- Mark trucks for maintenance
- Assign trucks to users

### User Management
- Add new users with roles and contact info
- View user assignments and history
- Activate/deactivate users

### Warehouse Overview
- Monitor warehouse capacity
- View trucks by location (in warehouse, out, maintenance)
- Quick assignment and return actions

### Assignment & Returns
- Assign available trucks to active users
- Return trucks with condition reporting
- View currently assigned trucks

### Logs & Reports
- Filter activity by date, user, or truck
- Export/print reports
- Comprehensive activity tracking

## Technical Details

- **Backend**: PHP 7.4+ with PDO for database operations
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3 with modern responsive design
- **Security**: Session-based authentication, SQL injection prevention
- **Compatibility**: Optimized for Laragon local development environment

## Customization

### Adding New Truck Types
Edit the type options in:
- `trucks.php` (line ~50)
- Database enum if needed

### Modifying Colors
Update CSS variables in `style.css`:
- Primary: `#8b5cf6`
- Secondary: `#a855f7`
- Background: `#1a1a1a`

### Adding New User Roles
Edit role options in:
- `users.php` (line ~70)

## Troubleshooting

### Database Connection Issues
1. Ensure Laragon MySQL service is running
2. Verify database name matches `config.php`
3. Check username/password in `config.php`

### Permission Issues
- Ensure PHP has read/write access to session files
- Check Laragon's PHP configuration

### Styling Issues
- Clear browser cache
- Verify `style.css` is loading correctly
- Check for CSS conflicts

## Future Enhancements

- Email notifications for assignments
- Advanced reporting with charts
- Mobile app integration
- GPS tracking integration
- Maintenance scheduling
- Multi-company support

## Support

This system is designed for educational and small business use. For production deployment, consider:
- Implementing proper password hashing
- Adding HTTPS security
- Setting up automated backups
- Implementing role-based permissions