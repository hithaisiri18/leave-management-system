<?php
session_start();
if(!isset($_SESSION['emp_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../config/database.php';

$emp_id = $_SESSION['emp_id'];
$year = date('Y');

// Get employee details
$emp_query = "SELECT * FROM employees WHERE emp_id = $emp_id";
$emp_result = $conn->query($emp_query);
$employee = $emp_result->fetch_assoc();

// Get leave balance
$balance_query = "SELECT lb.*, lt.leave_name FROM leave_balance lb 
                  JOIN leave_types lt ON lb.leave_type_id = lt.leave_type_id 
                  WHERE lb.emp_id = $emp_id AND lb.year = $year";
$balance_result = $conn->query($balance_query);

// Get pending leaves count
$pending_query = "SELECT COUNT(*) as pending_count FROM leave_applications 
                  WHERE emp_id = $emp_id AND status = 'pending'";
$pending_result = $conn->query($pending_query);
$pending = $pending_result->fetch_assoc();

// Get next month prediction
$next_month = date('Y-m-d', strtotime('+1 month'));
$prediction_query = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as future_leaves 
                     FROM leave_applications 
                     WHERE emp_id = $emp_id AND status = 'approved' 
                     AND start_date > NOW() AND start_date <= '$next_month'";
$prediction_result = $conn->query($prediction_query);
$prediction = $prediction_result->fetch_assoc();
$future_leaves = $prediction['future_leaves'] ?? 0;

// Calculate remaining after future leaves
$total_remaining = 0;
while($row = $balance_result->fetch_assoc()) {
    $total_remaining += $row['remaining_leaves'];
}
$balance_result->data_seek(0); // Reset pointer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Leave Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        nav h1 {
            font-size: 24px;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        nav a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .card h3 {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card .value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .card p {
            color: #666;
            font-size: 13px;
        }
        
        .card .progress {
            background: #eee;
            height: 8px;
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .card .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .leave-balance-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .leave-balance-table h3 {
            color: #333;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f5f5f5;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        table tbody tr:hover {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <h1>🎯 Leave Manager</h1>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="apply_leave.php">Apply Leave</a></li>
            <li><a href="my_leaves.php">My Leaves</a></li>
            <li><a href="analytics.php">Analytics</a></li>
            <?php if($_SESSION['emp_role'] == 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h2>Welcome, <?php echo htmlspecialchars($employee['name']); ?>! 👋</h2>
            <p><?php echo htmlspecialchars($employee['department']); ?> - <?php echo htmlspecialchars($employee['position']); ?></p>
        </div>
        
        <!-- Quick Stats -->
        <div class="cards-grid">
            <div class="card">
                <h3>📊 Total Leaves Available</h3>
                <div class="value"><?php echo $total_remaining; ?></div>
                <p>Days remaining this year</p>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo min($total_remaining * 5, 100); ?>%"></div>
                </div>
            </div>
            
            <div class="card">
                <h3>⏳ Pending Approvals</h3>
                <div class="value"><?php echo $pending['pending_count']; ?></div>
                <p>Awaiting manager approval</p>
            </div>
            
            <div class="card">
                <h3>📅 Next Month Leaves</h3>
                <div class="value"><?php echo $future_leaves; ?></div>
                <p>You'll have <?php echo ($total_remaining - $future_leaves); ?> days left</p>
            </div>
        </div>
        
        <!-- Leave Balance -->
        <div class="leave-balance-table">
            <h3>📋 Leave Balance Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Yearly Limit</th>
                        <th>Used</th>
                        <th>Remaining</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $balance_result = $conn->query($balance_query);
                    while($row = $balance_result->fetch_assoc()): 
                        $used = $row['used_leaves'];
                        $remaining = $row['remaining_leaves'];
                        $total = $used + $remaining;
                        $percentage = ($used / $total) * 100;
                    ?>
                    <tr>
                        <td><?php echo $row['leave_name']; ?></td>
                        <td><?php echo $total; ?> days</td>
                        <td><?php echo $used; ?> days</td>
                        <td><?php echo $remaining; ?> days</td>
                        <td>
                            <div class="progress" style="width: 100px;">
                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Action Buttons -->
        <div class="btn-group">
            <a href="apply_leave.php" class="btn btn-primary">➕ Apply New Leave</a>
            <a href="my_leaves.php" class="btn btn-secondary">📅 View All Leaves</a>
            <a href="analytics.php" class="btn btn-secondary">📊 View Analytics</a>
        </div>
    </div>
</body>
</html>