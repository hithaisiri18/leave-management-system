<?php
session_start();
if(!isset($_SESSION['emp_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../config/database.php';

$emp_id = $_SESSION['emp_id'];

// Get all leaves
$leaves_query = "SELECT la.*, lt.leave_name, e.name as backup_name 
                FROM leave_applications la
                JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
                LEFT JOIN employees e ON la.backup_emp_id = e.emp_id
                WHERE la.emp_id = $emp_id
                ORDER BY la.start_date DESC";
$leaves_result = $conn->query($leaves_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leaves - Leave Management</title>
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
            max-width: 1000px;
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
        
        .leave-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .leave-card.pending {
            border-left-color: #ff9800;
        }
        
        .leave-card.approved {
            border-left-color: #4caf50;
        }
        
        .leave-card.rejected {
            border-left-color: #f44336;
        }
        
        .leave-card.cancelled {
            border-left-color: #9e9e9e;
        }
        
        .leave-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .leave-type {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .leave-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .leave-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .leave-status.approved {
            background: #d4edda;
            color: #155724;
        }
        
        .leave-status.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .leave-status.cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .leave-dates {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .leave-dates strong {
            color: #333;
        }
        
        .leave-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-item {
            font-size: 14px;
        }
        
        .detail-item strong {
            color: #667eea;
        }
        
        .detail-item p {
            color: #666;
            margin-top: 3px;
        }
        
        .leave-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: #667eea;
            color: white;
        }
        
        .btn-edit:hover {
            background: #5568d3;
        }
        
        .btn-cancel {
            background: #f44336;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #da190b;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #666;
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
            <h2>📅 My Leave Applications</h2>
        </div>
        
        <?php if($leaves_result->num_rows > 0): ?>
            <?php while($leave = $leaves_result->fetch_assoc()): 
                $start = new DateTime($leave['start_date']);
                $end = new DateTime($leave['end_date']);
                $days = ($end->getTimestamp() - $start->getTimestamp()) / (60 * 60 * 24) + 1;
                $can_edit = ($leave['status'] == 'pending');
                $can_cancel = ($leave['status'] == 'approved' || $leave['status'] == 'pending');
            ?>
            <div class="leave-card <?php echo strtolower($leave['status']); ?>">
                <div class="leave-header">
                    <div>
                        <div class="leave-type"><?php echo $leave['leave_name']; ?></div>
                        <div class="leave-dates">
                            <strong><?php echo $start->format('M d, Y'); ?></strong> 
                            to 
                            <strong><?php echo $end->format('M d, Y'); ?></strong> 
                            (<?php echo (int)$days; ?> days)
                        </div>
                    </div>
                    <span class="leave-status <?php echo strtolower($leave['status']); ?>">
                        <?php echo ucfirst($leave['status']); ?>
                    </span>
                </div>
                
                <div class="leave-details">
                    <div class="detail-item">
                        <strong>Reason:</strong>
                        <p><?php echo htmlspecialchars($leave['reason']); ?></p>
                    </div>
                    <div class="detail-item">
                        <strong>Backup Employee:</strong>
                        <p><?php echo $leave['backup_name'] ? htmlspecialchars($leave['backup_name']) : 'Not assigned'; ?></p>
                    </div>
                    <?php if($leave['is_suspicious']): ?>
                    <div class="detail-item">
                        <strong>⚠️ Alert:</strong>
                        <p>Suspicious leave pattern detected</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="leave-actions">
                    <?php if($can_cancel): ?>
                        <a href="../backend/cancel_leave.php?leave_id=<?php echo $leave['leave_id']; ?>" 
                           class="btn btn-cancel" 
                           onclick="return confirm('Are you sure you want to cancel this leave?');">
                            ❌ Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>📭 No leave applications yet</h3>
                <p>Start by <a href="apply_leave.php" style="color: #667eea; text-decoration: underline;">applying for leave</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>