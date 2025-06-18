<?php
require_once 'config.php';
requireLogin();

// Get dashboard statistics
$stats = [];

// Total trucks
$stmt = $pdo->query("SELECT COUNT(*) FROM trucks");
$stats['total_trucks'] = $stmt->fetchColumn();

// Trucks in use
$stmt = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'in_use'");
$stats['trucks_in_use'] = $stmt->fetchColumn();

// Trucks in warehouse
$stmt = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'in_warehouse'");
$stats['trucks_in_warehouse'] = $stmt->fetchColumn();

// Recent activity
$stmt = $pdo->query("
    SELECT al.*, t.truck_id, u.name as user_name 
    FROM activity_logs al 
    LEFT JOIN trucks t ON al.truck_id = t.id 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.timestamp DESC 
    LIMIT 5
");
$recent_activity = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Truck Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🚚 Truck Management Dashboard</h1>
        </div>
    </div>

    <div class="container">
        <nav class="nav">
            <ul>
                <li><a href="index.php" class="active">📊 Dashboard</a></li>
                <li><a href="trucks.php">🚚 Trucks</a></li>
                <li><a href="users.php">👤 Users</a></li>
                <li><a href="warehouse.php">📦 Warehouse</a></li>
                <li><a href="assignments.php">📅 Assignments</a></li>
                <li><a href="logs.php">📝 Logs</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </nav>

        <!-- Hero Image -->
        <div class="hero-image dashboard-hero"></div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['total_trucks']; ?></span>
                <div class="stat-label">✅ Total Trucks</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['trucks_in_use']; ?></span>
                <div class="stat-label">🟥 Trucks in Use</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['trucks_in_warehouse']; ?></span>
                <div class="stat-label">🟩 Trucks in Warehouse</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h3>Quick Actions</h3>
            <div class="quick-actions">
                <a href="trucks.php?action=add" class="quick-action">
                    <span class="icon">➕</span>
                    Add New Truck
                </a>
                <a href="assignments.php?action=assign" class="quick-action">
                    <span class="icon">📋</span>
                    Assign Truck
                </a>
                <a href="assignments.php?action=return" class="quick-action">
                    <span class="icon">↩️</span>
                    Return Truck
                </a>
                <a href="users.php?action=add" class="quick-action">
                    <span class="icon">👤</span>
                    Add User
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <h3>Recent Activity</h3>
            <?php if (empty($recent_activity)): ?>
                <p>No recent activity.</p>
            <?php else: ?>
                <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-time">
                            <?php echo date('M j, Y H:i', strtotime($activity['timestamp'])); ?>
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($activity['truck_id']); ?></strong>
                            <?php echo htmlspecialchars($activity['action']); ?>
                            <?php if ($activity['user_name']): ?>
                                by <strong><?php echo htmlspecialchars($activity['user_name']); ?></strong>
                            <?php endif; ?>
                            <?php if ($activity['details']): ?>
                                - <?php echo htmlspecialchars($activity['details']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>