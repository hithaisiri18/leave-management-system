<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
if(!isset($_SESSION['emp_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../config/database.php';

$emp_id = $_SESSION['emp_id'];
$year = date('Y');

// Total leaves taken
$total_query = "SELECT COUNT(*) as total, SUM(DATEDIFF(end_date, start_date) + 1) as days 
               FROM leave_applications 
               WHERE emp_id = $emp_id AND status = 'approved' AND YEAR(start_date) = $year";
$total_result = $conn->query($total_query);
$total_data = $total_result->fetch_assoc();

// Leaves by type
$by_type_query = "SELECT lt.leave_name, COUNT(*) as count, SUM(DATEDIFF(la.end_date, la.start_date) + 1) as days
                 FROM leave_applications la
                 JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
                 WHERE la.emp_id = $emp_id AND la.status = 'approved' AND YEAR(la.start_date) = $year
                 GROUP BY lt.leave_name";
$by_type_result = $conn->query($by_type_query);

// Most frequent leave day
$frequent_day_query = "SELECT DAYNAME(start_date) as day, COUNT(*) as count
                      FROM leave_applications 
                      WHERE emp_id = $emp_id AND status = 'approved' AND YEAR(start_date) = $year
                      GROUP BY DAYNAME(start_date)
                      ORDER BY count DESC
                      LIMIT 1";
$frequent_day_result = $conn->query($frequent_day_query);
$frequent_day = $frequent_day_result->fetch_assoc();

// Weekend adjacent leaves
$weekend_query = "SELECT COUNT(*) as count FROM leave_applications 
                 WHERE emp_id = $emp_id AND status = 'approved'
                 AND (DAYOFWEEK(start_date) IN (5,6) OR DAYOFWEEK(end_date) IN (5,6))
                 AND YEAR(start_date) = $year";
$weekend_result = $conn->query($weekend_query);
$weekend_data = $weekend_result->fetch_assoc();

// Last minute leaves (< 48 hours notice)
$lastminute_query = "SELECT COUNT(*) as count FROM leave_applications 
                    WHERE emp_id = $emp_id AND status = 'approved'
                    AND DATEDIFF(start_date, applied_on) < 2
                    AND YEAR(start_date) = $year";
$lastminute_result = $conn->query($lastminute_query);
$lastminute_data = $lastminute_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Leave Management</title>
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
            margin: 30px auto;
            padding: 20px;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #333;
            font-size: 28px;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .analytics-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .analytics-card h3 {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .table-card h3 {
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
        
        .progress {
            background: #eee;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
        }
        
        .alert-info {
            background: #eef;
            border-left: 4px solid #667eea;
            padding: 12px;
            border-radius: 5px;
            margin-top: 10px;
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
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <div class="header">
            <h2>📊 Leave Pattern Analytics - <?php echo $year; ?></h2>
        </div>
        
        <!-- Stats Cards -->
        <div class="analytics-grid">
            <div class="analytics-card">
                <h3>📈 Total Leaves Taken</h3>
                <div class="stat-value"><?php echo $total_data['total'] ?? 0; ?></div>
                <div class="stat-label"><?php echo ($total_data['days'] ?? 0) . " days total"; ?></div>
            </div>
            
            <div class="analytics-card">
                <h3>🗓️ Most Frequent Day</h3>
                <div class="stat-value"><?php echo $frequent_day['day'] ?? 'N/A'; ?></div>
                <div class="stat-label"><?php echo ($frequent_day['count'] ?? 0) . " times"; ?></div>
            </div>
            
            <div class="analytics-card">
                <h3>🛑 Weekend Adjacent</h3>
                <div class="stat-value"><?php echo $weekend_data['count']; ?></div>
                <div class="stat-label">Leaves near weekends</div>
            </div>
            
            <div class="analytics-card">
                <h3>⚡ Last Minute</h3>
                <div class="stat-value"><?php echo $lastminute_data['count']; ?></div>
                <div class="stat-label">Applied < 48 hours before</div>
            </div>
        </div>
        
        <!-- Leave Type Breakdown -->
        <div class="table-card">
            <h3>📋 Leaves by Type</h3>
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Count</th>
                        <th>Days</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $by_type_result = $conn->query($by_type_query);
                    while($row = $by_type_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $row['leave_name']; ?></td>
                        <td><?php echo $row['count']; ?> applications</td>
                        <td><?php echo $row['days']; ?> days</td>
                        <td>
                            <div class="progress" style="width: 100px;">
                                <div class="progress-bar" style="width: <?php echo min(($row['days'] / 20) * 100, 100); ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Insights -->
        <div class="table-card">
            <h3>💡 Insights & Observations</h3>
            <div class="alert-info">
                <strong>📌 Leave Pattern Summary:</strong><br>
                <?php 
                if($frequent_day['day']) {
                    echo "You often take leaves on <strong>" . $frequent_day['day'] . "s</strong><br>";
                }
                if($weekend_data['count'] > 3) {
                    echo "⚠️ You have taken " . $weekend_data['count'] . " leaves adjacent to weekends<br>";
                }
                if($lastminute_data['count'] > 2) {
                    echo "⚠️ You frequently apply for last-minute leaves (" . $lastminute_data['count'] . " times)<br>";
                }
                echo "You've taken a total of " . ($total_data['days'] ?? 0) . " days off this year<br>";
                ?>
            </div>
        </div>
    </div>
</body>
</html>