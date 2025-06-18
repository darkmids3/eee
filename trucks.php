<?php
require_once 'config.php';
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_truck':
                $truck_id = $_POST['truck_id'];
                $model = $_POST['model'];
                $type = $_POST['type'];
                
                $stmt = $pdo->prepare("INSERT INTO trucks (truck_id, model, type) VALUES (?, ?, ?)");
                $stmt->execute([$truck_id, $model, $type]);
                
                // Log activity
                $truck_db_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO activity_logs (truck_id, action, details) VALUES (?, 'added', 'New truck added to system')");
                $stmt->execute([$truck_db_id]);
                
                header('Location: trucks.php?success=added');
                exit;
                break;
                
            case 'update_status':
                $truck_id = $_POST['truck_id'];
                $new_status = $_POST['new_status'];
                
                $stmt = $pdo->prepare("UPDATE trucks SET status = ?, assigned_user_id = NULL WHERE id = ?");
                $stmt->execute([$new_status, $truck_id]);
                
                // Log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (truck_id, action, details) VALUES (?, 'status_changed', 'Status changed to " . $new_status . "')");
                $stmt->execute([$truck_id]);
                
                header('Location: trucks.php?success=updated');
                exit;
                break;
        }
    }
}

// Get all trucks with user information
$stmt = $pdo->query("
    SELECT t.*, u.name as assigned_user_name 
    FROM trucks t 
    LEFT JOIN users u ON t.assigned_user_id = u.id 
    ORDER BY t.truck_id
");
$trucks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truck Management - Truck Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🚚 Truck Management</h1>
        </div>
    </div>

    <div class="container">
        <nav class="nav">
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="trucks.php" class="active">🚚 Trucks</a></li>
                <li><a href="users.php">👤 Users</a></li>
                <li><a href="warehouse.php">📦 Warehouse</a></li>
                <li><a href="assignments.php">📅 Assignments</a></li>
                <li><a href="logs.php">📝 Logs</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </nav>

        <!-- Hero Image -->
        <div class="hero-image trucks-hero"></div>

        <?php if (isset($_GET['success'])): ?>
            <div class="card" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3);">
                <p style="color: #22c55e; margin: 0;">
                    <?php 
                    switch ($_GET['success']) {
                        case 'added': echo 'Truck added successfully!'; break;
                        case 'updated': echo 'Truck status updated successfully!'; break;
                        default: echo 'Operation completed successfully!';
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Add New Truck Form -->
        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
        <div class="card">
            <h3>Add New Truck</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_truck">
                <div class="form-group">
                    <label for="truck_id">Truck ID:</label>
                    <input type="text" id="truck_id" name="truck_id" placeholder="e.g., TRK-006" required>
                </div>
                <div class="form-group">
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" placeholder="e.g., Ford F-150" required>
                </div>
                <div class="form-group">
                    <label for="type">Type:</label>
                    <select id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="Pickup">Pickup</option>
                        <option value="Van">Van</option>
                        <option value="Box Truck">Box Truck</option>
                        <option value="Heavy Duty">Heavy Duty</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Truck</button>
                <a href="trucks.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>All Trucks</h3>
                <a href="trucks.php?action=add" class="btn btn-primary">➕ Add New Truck</a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Truck ID</th>
                            <th>Model & Type</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Last Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trucks as $truck): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($truck['truck_id']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($truck['model']); ?><br>
                                <small style="color: #888;"><?php echo htmlspecialchars($truck['type']); ?></small>
                            </td>
                            <td>
                                <span class="status <?php echo str_replace('_', '-', $truck['status']); ?>">
                                    <?php 
                                    switch ($truck['status']) {
                                        case 'in_warehouse': echo '🟢 In Warehouse'; break;
                                        case 'in_use': echo '🔴 In Use'; break;
                                        case 'maintenance': echo '🟡 Maintenance'; break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($truck['assigned_user_name']): ?>
                                    <?php echo htmlspecialchars($truck['assigned_user_name']); ?>
                                <?php else: ?>
                                    <span style="color: #888;">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($truck['last_used']): ?>
                                    <?php echo date('M j, Y H:i', strtotime($truck['last_used'])); ?>
                                <?php else: ?>
                                    <span style="color: #888;">Never used</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($truck['status'] === 'in_use'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="truck_id" value="<?php echo $truck['id']; ?>">
                                        <input type="hidden" name="new_status" value="in_warehouse">
                                        <button type="submit" class="btn btn-small btn-secondary">Mark as In</button>
                                    </form>
                                <?php else: ?>
                                    <a href="assignments.php?assign_truck=<?php echo $truck['id']; ?>" class="btn btn-small btn-primary">Assign</a>
                                <?php endif; ?>
                                
                                <?php if ($truck['status'] !== 'maintenance'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="truck_id" value="<?php echo $truck['id']; ?>">
                                        <input type="hidden" name="new_status" value="maintenance">
                                        <button type="submit" class="btn btn-small" style="background: rgba(251, 191, 36, 0.2); color: #fbbf24;">Maintenance</button>
                                    </form>
                                <?php endif; ?>
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