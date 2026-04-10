<?php
session_start();
if(!isset($_SESSION['emp_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../config/database.php';

$emp_id = $_SESSION['emp_id'];
$year = date('Y');
$error = '';
$success = '';

// Get leave types
$leave_types_query = "SELECT * FROM leave_types";
$leave_types_result = $conn->query($leave_types_query);

// Get employees for backup assignment
$emp_query = "SELECT emp_id, name FROM employees WHERE emp_id != $emp_id ORDER BY name";
$emp_result = $conn->query($emp_query);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = escape_string($_POST['start_date']);
    $end_date = escape_string($_POST['end_date']);
    $leave_type_id = escape_string($_POST['leave_type_id']);
    $reason = escape_string($_POST['reason']);
    $backup_emp_id = $_POST['backup_emp_id'] ? escape_string($_POST['backup_emp_id']) : NULL;
    
    // Validate dates
    if(strtotime($end_date) < strtotime($start_date)) {
        $error = "❌ End date must be after start date!";
    } else {
        // Calculate days
        $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
        
        // Check leave limit
        $limit_query = "SELECT lt.yearly_limit, lb.remaining_leaves 
                       FROM leave_types lt
                       LEFT JOIN leave_balance lb ON lt.leave_type_id = lb.leave_type_id 
                       AND lb.emp_id = $emp_id AND lb.year = $year
                       WHERE lt.leave_type_id = $leave_type_id";
        $limit_result = $conn->query($limit_query);
        $limit_data = $limit_result->fetch_assoc();
        
        $remaining = $limit_data['remaining_leaves'] ?? $limit_data['yearly_limit'];
        
        if($days > $remaining) {
            $error = "❌ You've reached your limit! Only $remaining days available.";
        } else {
            // Check for suspicious patterns
            $is_suspicious = 0;
            
            // Check if leave is adjacent to weekend
            $start_day = date('N', strtotime($start_date)); // 1-7 (Monday-Sunday)
            $end_day = date('N', strtotime($end_date));
            
            if($start_day == 5 || $start_day == 6 || $end_day == 5 || $end_day == 6) {
                // Check how many weekend-adjacent leaves in last 3 months
                $check_query = "SELECT COUNT(*) as count FROM leave_applications 
                               WHERE emp_id = $emp_id 
                               AND status = 'approved'
                               AND start_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                               AND DAYOFWEEK(start_date) IN (5,6)";
                $check_result = $conn->query($check_query);
                $check_data = $check_result->fetch_assoc();
                
                if($check_data['count'] >= 3) {
                    $is_suspicious = 1;
                }
            }
            
            // Check for last-minute applications
            if(strtotime($start_date) - time() < (48 * 60 * 60)) {
                $is_suspicious = 1;
            }
            
            // Insert leave application
            $insert_query = "INSERT INTO leave_applications 
                           (emp_id, leave_type_id, start_date, end_date, reason, backup_emp_id, is_suspicious) 
                           VALUES 
                           ($emp_id, $leave_type_id, '$start_date', '$end_date', '$reason', $backup_emp_id, $is_suspicious)";
            
            if($conn->query($insert_query)) {
                $leave_id = $conn->insert_id;
                
                // Create notification
                $notif_query = "INSERT INTO notifications (emp_id, leave_id, message) 
                               VALUES ($emp_id, $leave_id, 'Your leave application has been submitted')";
                $conn->query($notif_query);
                
                // Notify manager
                $manager_query = "SELECT manager_id FROM employees WHERE emp_id = $emp_id";
                $manager_result = $conn->query($manager_query);
                $manager = $manager_result->fetch_assoc();
                
                if($manager['manager_id']) {
                    $manager_notif = "INSERT INTO notifications (emp_id, leave_id, message) 
                                     VALUES ({$manager['manager_id']}, $leave_id, 'New leave application to review')";
                    $conn->query($manager_notif);
                }
                
                $success = "✅ Leave application submitted! " . ($is_suspicious ? "⚠️ Your leave pattern looks suspicious." : "");
            } else {
                $error = "❌ Error submitting leave: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave - Leave Management</title>
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
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-card h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .form-card p {
            color: #666;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        
        .success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .info {
            background: #eef;
            border: 1px solid #99d;
            color: #335;
            padding: 12px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 13px;
            line-height: 1.6;
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
        <div class="form-card">
            <h2>📋 Apply for Leave</h2>
            <p>Submit your leave request for manager approval</p>
            
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="leave_type">🏷️ Leave Type</label>
                        <select name="leave_type_id" id="leave_type" required>
                            <option value="">Select Leave Type</option>
                            <?php while($row = $leave_types_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['leave_type_id']; ?>">
                                    <?php echo $row['leave_name']; ?> (<?php echo $row['yearly_limit']; ?>/year)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration">📅 Duration</label>
                        <input type="text" id="duration" placeholder="0 days" readonly style="background: #f5f5f5;">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">📍 Start Date</label>
                        <input type="date" name="start_date" id="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">📍 End Date</label>
                        <input type="date" name="end_date" id="end_date" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reason">📝 Reason for Leave</label>
                    <textarea name="reason" id="reason" placeholder="Briefly explain your leave reason..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="backup">👥 Assign Backup Employee (Optional)</label>
                    <select name="backup_emp_id" id="backup">
                        <option value="">-- No Backup --</option>
                        <?php 
                        $emp_result = $conn->query($emp_query);
                        while($row = $emp_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $row['emp_id']; ?>">
                                <?php echo $row['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">✅ Submit Application</button>
            </form>
            
            <div class="info">
                <strong>💡 Tips:</strong><br>
                • Apply at least 2 days in advance<br>
                • Select a backup employee to cover your work<br>
                • Check your leave balance before applying<br>
                • Applications adjacent to weekends may be flagged
            </div>
        </div>
    </div>
    
    <script>
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const duration = document.getElementById('duration');
        
        function calculateDays() {
            if(startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                const days = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                duration.value = days + ' days';
            }
        }
        
        startDate.addEventListener('change', calculateDays);
        endDate.addEventListener('change', calculateDays);
    </script>
</body>
</html>