<?php
require_once 'config.php';
requireLogin();

// Get warehouse statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'in_warehouse'");
$trucks_in_warehouse = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'in_use'");
$trucks_out = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM trucks WHERE status = 'maintenance'");
$trucks_maintenance = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM trucks");
$total_trucks = $stmt->fetchColumn();

// Get trucks by status
$stmt = $pdo->query("
    SELECT t.*, u.name as assigned_user_name 
    FROM trucks t 
    LEFT JOIN users u ON t.assigned_user_id = u.id 
    WHERE t.status = 'in_warehouse'
    ORDER BY t.truck_id
");
$warehouse_trucks = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT t.*, u.name as assigned_user_name 
    FROM trucks t 
    LEFT JOIN users u ON t.assigned_user_id = u.id 
    WHERE t.status = 'in_use'
    ORDER BY t.truck_id
");
$out_trucks = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT t.*, u.name as assigned_user_name 
    FROM trucks t 
    LEFT JOIN users u ON t.assigned_user_id = u.id 
    WHERE t.status = 'maintenance'
    ORDER BY t.truck_id
");
$maintenance_trucks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Overview - Truck Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>📦 Warehouse Overview</h1>
        </div>
    </div>

    <div class="container">
        <nav class="nav">
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="trucks.php">🚚 Trucks</a></li>
                <li><a href="users.php">👤 Users</a></li>
                <li><a href="warehouse.php" class="active">📦 Warehouse</a></li>
                <li><a href="assignments.php">📅 Assignments</a></li>
                <li><a href="logs.php">📝 Logs</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </nav>

        <!-- Hero Image -->
        <div class="hero-image warehouse-hero"></div>

        <!-- Warehouse Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo $trucks_in_warehouse; ?>/<?php echo $total_trucks; ?></span>
                <div class="stat-label">📦 Warehouse Capacity</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $trucks_out; ?></span>
                <div class="stat-label">🚛 Trucks Out</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $trucks_maintenance; ?></span>
                <div class="stat-label">🔧 In Maintenance</div>
            </div>
        </div>

        <!-- Trucks in Warehouse -->
        <div class="card">
            <h3>🟢 Trucks in Warehouse (<?php echo count($warehouse_trucks); ?>)</h3>
            <?php if (empty($warehouse_trucks)): ?>
                <p style="color: #888;">No trucks currently in warehouse.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Truck ID</th>
                                <th>Model & Type</th>
                                <th>Condition</th>
                                <th>Last Used</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($warehouse_trucks as $truck): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($truck['truck_id']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($truck['model']); ?><br>
                                    <small style="color: #888;"><?php echo htmlspecialchars($truck['type']); ?></small>
                                </td>
                                <td>
                                    <span class="status <?php echo $truck['condition_status'] === 'good' ? 'in-warehouse' : ($truck['condition_status'] === 'fair' ? 'maintenance' : 'in-use'); ?>">
                                        <?php 
                                        switch ($truck['condition_status']) {
                                            case 'good': echo '✅ Good'; break;
                                            case 'fair': echo '⚠️ Fair'; break;
                                            case 'needs_repair': echo '❌ Needs Repair'; break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($truck['last_used']): ?>
                                        <?php echo date('M j, Y', strtotime($truck['last_used'])); ?>
                                    <?php else: ?>
                                        <span style="color: #888;">Never used</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="assignments.php?assign_truck=<?php echo $truck['id']; ?>" class="btn btn-small btn-primary">Assign</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Trucks Currently Out -->
        <div class="card">
            <h3>🔴 Trucks Currently Out (<?php echo count($out_trucks); ?>)</h3>
            <?php if (empty($out_trucks)): ?>
                <p style="color: #888;">No trucks currently out.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Truck ID</th>
                                <th>Model & Type</th>
                                <th>Assigned To</th>
                                <th>Out Since</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($out_trucks as $truck): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($truck['truck_id']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($truck['model']); ?><br>
                                    <small style="color: #888;"><?php echo htmlspecialchars($truck['type']); ?></small>
                                </td>
                                <td>
                                    <?php if ($truck['assigned_user_name']): ?>
                                        <strong><?php echo htmlspecialchars($truck['assigned_user_name']); ?></strong>
                                    <?php else: ?>
                                        <span style="color: #888;">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($truck['updated_at']): ?>
                                        <?php echo date('M j, Y H:i', strtotime($truck['updated_at'])); ?>
                                    <?php else: ?>
                                        <span style="color: #888;">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="assignments.php?return_truck=<?php echo $truck['id']; ?>" class="btn btn-small btn-secondary">Return</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Trucks in Maintenance -->
        <?php if (!empty($maintenance_trucks)): ?>
        <div class="card">
            <h3>🔧 Trucks in Maintenance (<?php echo count($maintenance_trucks); ?>)</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Truck ID</th>
                            <th>Model & Type</th>
                            <th>Condition</th>
                            <th>Since</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($maintenance_trucks as $truck): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($truck['truck_id']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($truck['model']); ?><br>
                                <small style="color: #888;"><?php echo htmlspecialchars($truck['type']); ?></small>
                            </td>
                            <td>
                                <span class="status maintenance">🔧 Maintenance</span>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($truck['updated_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>