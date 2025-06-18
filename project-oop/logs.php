<?php
require_once 'config.php';
requireLogin();

// Handle filters
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$user_filter = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$truck_filter = isset($_GET['truck_id']) ? $_GET['truck_id'] : '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($date_filter) {
    $where_conditions[] = "DATE(al.timestamp) = ?";
    $params[] = $date_filter;
}

if ($user_filter) {
    $where_conditions[] = "al.user_id = ?";
    $params[] = $user_filter;
}

if ($truck_filter) {
    $where_conditions[] = "al.truck_id = ?";
    $params[] = $truck_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get activity logs with filters
$stmt = $pdo->prepare("
    SELECT al.*, t.truck_id, u.name as user_name 
    FROM activity_logs al 
    LEFT JOIN trucks t ON al.truck_id = t.id 
    LEFT JOIN users u ON al.user_id = u.id 
    $where_clause
    ORDER BY al.timestamp DESC 
    LIMIT 100
");
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get all users and trucks for filter dropdowns
$stmt = $pdo->query("SELECT id, name FROM users ORDER BY name");
$all_users = $stmt->fetchAll();

$stmt = $pdo->query("SELECT id, truck_id FROM trucks ORDER BY truck_id");
$all_trucks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs & Reports - Truck Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>📝 Logs & Reports</h1>
        </div>
    </div>

    <div class="container">
        <nav class="nav">
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="trucks.php">🚚 Trucks</a></li>
                <li><a href="users.php">👤 Users</a></li>
                <li><a href="warehouse.php">📦 Warehouse</a></li>
                <li><a href="assignments.php">📅 Assignments</a></li>
                <li><a href="logs.php" class="active">📝 Logs</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </nav>

        <!-- Filters -->
        <div class="card">
            <h3>🔍 Filter Logs</h3>
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="user_id">User:</label>
                    <select id="user_id" name="user_id">
                        <option value="">All Users</option>
                        <?php foreach ($all_users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="truck_id">Truck:</label>
                    <select id="truck_id" name="truck_id">
                        <option value="">All Trucks</option>
                        <?php foreach ($all_trucks as $truck): ?>
                            <option value="<?php echo $truck['id']; ?>" <?php echo $truck_filter == $truck['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($truck['truck_id']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="logs.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <!-- Activity Logs -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>Activity Log (Showing <?php echo count($logs); ?> entries)</h3>
                <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
            </div>
            
            <?php if (empty($logs)): ?>
                <p style="color: #888;">No activity found for the selected filters.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Truck</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('M j, Y', strtotime($log['timestamp'])); ?></strong><br>
                                    <small style="color: #888;"><?php echo date('H:i:s', strtotime($log['timestamp'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($log['truck_id']): ?>
                                        <strong><?php echo htmlspecialchars($log['truck_id']); ?></strong>
                                    <?php else: ?>
                                        <span style="color: #888;">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['user_name']): ?>
                                        <?php echo htmlspecialchars($log['user_name']); ?>
                                    <?php else: ?>
                                        <span style="color: #888;">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status <?php 
                                        switch ($log['action']) {
                                            case 'assigned': echo 'in-use'; break;
                                            case 'returned': echo 'in-warehouse'; break;
                                            case 'maintenance': echo 'maintenance'; break;
                                            default: echo 'in-warehouse';
                                        }
                                    ?>">
                                        <?php 
                                        switch ($log['action']) {
                                            case 'assigned': echo '📤 Assigned'; break;
                                            case 'returned': echo '📥 Returned'; break;
                                            case 'maintenance': echo '🔧 Maintenance'; break;
                                            case 'added': echo '➕ Added'; break;
                                            case 'status_changed': echo '🔄 Status Changed'; break;
                                            default: echo ucfirst($log['action']);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['details']): ?>
                                        <?php echo htmlspecialchars($log['details']); ?>
                                    <?php else: ?>
                                        <span style="color: #888;">No details</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Print Styles -->
        <style>
            @media print {
                .nav, .header, button, .btn { display: none !important; }
                body { background: white !important; color: black !important; }
                .card { border: 1px solid #000 !important; background: white !important; }
                .table-container { border: none !important; }
                table { border-collapse: collapse !important; }
                th, td { border: 1px solid #000 !important; color: black !important; }
                .status { border: 1px solid #000 !important; }
            }
        </style>
    </div>
</body>
</html>