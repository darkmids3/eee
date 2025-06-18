<?php
require_once 'config.php';
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $name = $_POST['name'];
                $role = $_POST['role'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                
                $stmt = $pdo->prepare("INSERT INTO users (name, role, phone, email) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $role, $phone, $email]);
                
                header('Location: users.php?success=added');
                exit;
                break;
                
            case 'toggle_status':
                $user_id = $_POST['user_id'];
                $current_status = $_POST['current_status'];
                $new_status = ($current_status === 'active') ? 'inactive' : 'active';
                
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $user_id]);
                
                header('Location: users.php?success=updated');
                exit;
                break;
        }
    }
}

// Get all users with their current truck assignments
$stmt = $pdo->query("
    SELECT u.*, t.truck_id as current_truck 
    FROM users u 
    LEFT JOIN trucks t ON u.id = t.assigned_user_id AND t.status = 'in_use'
    ORDER BY u.name
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Truck Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>👤 User Management</h1>
        </div>
    </div>

    <div class="container">
        <nav class="nav">
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="trucks.php">🚚 Trucks</a></li>
                <li><a href="users.php" class="active">👤 Users</a></li>
                <li><a href="warehouse.php">📦 Warehouse</a></li>
                <li><a href="assignments.php">📅 Assignments</a></li>
                <li><a href="logs.php">📝 Logs</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </nav>

        <?php if (isset($_GET['success'])): ?>
            <div class="card" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3);">
                <p style="color: #22c55e; margin: 0;">
                    <?php 
                    switch ($_GET['success']) {
                        case 'added': echo 'User added successfully!'; break;
                        case 'updated': echo 'User status updated successfully!'; break;
                        default: echo 'Operation completed successfully!';
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Add New User Form -->
        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
        <div class="card">
            <h3>Add New User</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="Driver">Driver</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Manager">Manager</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" placeholder="555-0123">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="user@company.com">
                </div>
                <button type="submit" class="btn btn-primary">Add User</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>All Users</h3>
                <a href="users.php?action=add" class="btn btn-primary">➕ Add New User</a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Current Truck</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <?php if ($user['phone']): ?>
                                    📞 <?php echo htmlspecialchars($user['phone']); ?><br>
                                <?php endif; ?>
                                <?php if ($user['email']): ?>
                                    ✉️ <?php echo htmlspecialchars($user['email']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['current_truck']): ?>
                                    <span class="status in-use"><?php echo htmlspecialchars($user['current_truck']); ?></span>
                                <?php else: ?>
                                    <span style="color: #888;">No truck assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status <?php echo $user['status'] === 'active' ? 'in-warehouse' : 'maintenance'; ?>">
                                    <?php echo $user['status'] === 'active' ? '✅ Active' : '❌ Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                    <button type="submit" class="btn btn-small btn-secondary">
                                        <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                                <a href="logs.php?user_id=<?php echo $user['id']; ?>" class="btn btn-small btn-secondary">View History</a>
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