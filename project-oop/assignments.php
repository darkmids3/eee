<?php
require_once 'config.php';
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'assign_truck':
                $truck_id = $_POST['truck_id'];
                $user_id = $_POST['user_id'];
                $reason = $_POST['reason'];
                
                // Update truck status and assign user
                $stmt = $pdo->prepare("UPDATE trucks SET status = 'in_use', assigned_user_id = ?, last_used = NOW() WHERE id = ?");
                $stmt->execute([$user_id, $truck_id]);
                
                // Log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (truck_id, user_id, action, details) VALUES (?, ?, 'assigned', ?)");
                $stmt->execute([$truck_id, $user_id, $reason]);
                
                header('Location: assignments.php?success=assigned');
                exit;
                break;
                
            case 'return_truck':
                $truck_id = $_POST['truck_id'];
                $condition = $_POST['condition'];
                $notes = $_POST['notes'];
                
                // Get current user assignment
                $stmt = $pdo->prepare("SELECT assigned_user_id FROM trucks WHERE id = ?");
                $stmt->execute([$truck_id]);
                $current_user = $stmt->fetchColumn();
                
                // Update truck status
                $stmt = $pdo->prepare("UPDATE trucks SET status = 'in_warehouse', assigned_user_id = NULL, condition_status = ? WHERE id = ?");
                $stmt->execute([$condition, $truck_id]);
                
                // Log activity
                $details = "Truck returned in " . $condition . " condition";
                if ($notes) $details .= " - " . $notes;
                $stmt = $pdo->prepare("INSERT INTO activity_logs (truck_id, user_id, action, details) VALUES (?, ?, 'returned', ?)");
                $stmt->execute([$truck_id, $current_user, $details]);
                
                header('Location: assignments.php?success=returned');
                exit;
                break;
        }
    }
}

// Get available trucks for assignment
$stmt = $pdo->query("SELECT * FROM trucks WHERE status = 'in_warehouse' ORDER BY truck_id");
$available_trucks = $stmt->fetchAll();

// Get active users
$stmt = $pdo->query("SELECT * FROM users WHERE status = 'active' ORDER BY name");
$active_users = $stmt->fetchAll();

// Get trucks currently in use
$stmt = $pdo->query("
    SELECT t.*, u.name as assigned_user_name 
    FROM trucks t 
    LEFT JOIN users u ON t.assigned_user_id = u.id 
    WHERE t.status = 'in_use' 
    ORDER BY t.truck_id
");
$trucks_in_use = $stmt->fetchAll();

// Check for pre-selected truck or return
$preselect_assign = isset($_GET['assign_truck']) ? $_GET['assign_truck'] : null;
$preselect_return = isset($_GET['return_truck']) ? $_GET['return_truck'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments & Returns - Truck Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>📅 Assignments & Returns</h1>
        </div>
    </div>

    <div class="container">
        <nav class="nav">
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="trucks.php">🚚 Trucks</a></li>
                <li><a href="users.php">👤 Users</a></li>
                <li><a href="warehouse.php">📦 Warehouse</a></li>
                <li><a href="assignments.php" class="active">📅 Assignments</a></li>
                <li><a href="logs.php">📝 Logs</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($_GET['success'])): ?>
            <div class="card" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3);">
                <p style="color: #22c55e; margin: 0;">
                    <?php 
                    switch ($_GET['success']) {
                        case 'assigned': echo 'Truck assigned successfully!'; break;
                        case 'returned': echo 'Truck returned successfully!'; break;
                        default: echo 'Operation completed successfully!';
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
            <!-- Assign Truck Form -->
            <div class="card">
                <h3>📋 Assign Truck</h3>
                <?php if (empty($available_trucks)): ?>
                    <p style="color: #888;">No trucks available for assignment.</p>
                <?php elseif (empty($active_users)): ?>
                    <p style="color: #888;">No active users available.</p>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="assign_truck">
                        <div class="form-group">
                            <label for="truck_id">Select Truck:</label>
                            <select id="truck_id" name="truck_id" required>
                                <option value="">Choose a truck</option>
                                <?php foreach ($available_trucks as $truck): ?>
                                    <option value="<?php echo $truck['id']; ?>" <?php echo $preselect_assign == $truck['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($truck['truck_id']); ?> - <?php echo htmlspecialchars($truck['model']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">Assign to User:</label>
                            <select id="user_id" name="user_id" required>
                                <option value="">Choose a user</option>
                                <?php foreach ($active_users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['role']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reason">Reason/Job Description:</label>
                            <textarea id="reason" name="reason" rows="3" placeholder="e.g., Delivery route, Client pickup, etc."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Assign Truck</button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Return Truck Form -->
            <div class="card">
                <h3>↩️ Return Truck</h3>
                <?php if (empty($trucks_in_use)): ?>
                    <p style="color: #888;">No trucks currently out for return.</p>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="return_truck">
                        <div class="form-group">
                            <label for="return_truck_id">Select Truck to Return:</label>
                            <select id="return_truck_id" name="truck_id" required>
                                <option value="">Choose a truck</option>
                                <?php foreach ($trucks_in_use as $truck): ?>
                                    <option value="<?php echo $truck['id']; ?>" <?php echo $preselect_return == $truck['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($truck['truck_id']); ?> - <?php echo htmlspecialchars($truck['model']); ?>
                                        <?php if ($truck['assigned_user_name']): ?>
                                            (<?php echo htmlspecialchars($truck['assigned_user_name']); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="condition">Truck Condition:</label>
                            <select id="condition" name="condition" required>
                                <option value="">Select condition</option>
                                <option value="good">✅ Good - No issues</option>
                                <option value="fair">⚠️ Fair - Minor issues</option>
                                <option value="needs_repair">❌ Needs Repair - Major issues</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes (optional):</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Any issues, damage, or notes about the truck condition..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Return Truck</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Currently Assigned Trucks -->
        <?php if (!empty($trucks_in_use)): ?>
        <div class="card">
            <h3>🚛 Currently Assigned Trucks</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Truck ID</th>
                            <th>Model</th>
                            <th>Assigned To</th>
                            <th>Assigned On</th>
                            <th>Quick Return</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trucks_in_use as $truck): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($truck['truck_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($truck['model']); ?></td>
                            <td>
                                <?php if ($truck['assigned_user_name']): ?>
                                    <?php echo htmlspecialchars($truck['assigned_user_name']); ?>
                                <?php else: ?>
                                    <span style="color: #888;">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y H:i', strtotime($truck['last_used'])); ?></td>
                            <td>
                                <a href="assignments.php?return_truck=<?php echo $truck['id']; ?>" class="btn btn-small btn-secondary">Return</a>
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