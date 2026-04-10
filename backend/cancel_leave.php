<?php
session_start();
include '../config/database.php';

if(!isset($_SESSION['emp_id']) || !isset($_GET['leave_id'])) {
    header("Location: ../pages/my_leaves.php");
    exit();
}

$leave_id = escape_string($_GET['leave_id']);
$emp_id = $_SESSION['emp_id'];

// Verify ownership
$verify_query = "SELECT * FROM leave_applications WHERE leave_id = $leave_id AND emp_id = $emp_id";
$verify_result = $conn->query($verify_query);

if($verify_result->num_rows > 0) {
    // Update status
    $update_query = "UPDATE leave_applications SET status = 'cancelled', modified_at = NOW() WHERE leave_id = $leave_id";
    $conn->query($update_query);
    
    // Create notification
    $notif_query = "INSERT INTO notifications (emp_id, leave_id, message) 
                   VALUES ($emp_id, $leave_id, 'Your leave has been cancelled')";
    $conn->query($notif_query);
}

header("Location: ../pages/my_leaves.php");
exit();
?>