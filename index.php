<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

// If already logged in, redirect to dashboard
if(isset($_SESSION['emp_id'])) {
    header("Location: pages/dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'config/database.php';
    
    $email = escape_string($_POST['email']);
    $password = md5($_POST['password']);
    
    $query = "SELECT * FROM employees WHERE email='$email' AND password='$password'";
    $result = $conn->query($query);
    
    if($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        $_SESSION['emp_id'] = $employee['emp_id'];
        $_SESSION['emp_name'] = $employee['name'];
        $_SESSION['emp_email'] = $employee['email'];
        $_SESSION['emp_role'] = $employee['role'];
        
        header("Location: pages/dashboard.php");
        exit();
    } else {
        $error = "❌ Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Management - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        
        .login-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .login-container p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
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
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
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
        
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        
        .demo-creds {
            background: #eef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 13px;
            color: #555;
            border-left: 4px solid #667eea;
        }
        
        .demo-creds strong {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }
        
        .demo-creds p {
            margin: 5px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🎯 Leave Manager</h1>
        <p>Employee Leave Management System</p>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">📧 Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">🔐 Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="demo-creds">
            <strong>Demo Credentials:</strong>
            <p><strong>Admin:</strong> admin@company.com / admin123</p>
            <p><strong>Employee:</strong> john@company.com / john123</p>
            <p><strong>Manager:</strong> jane@company.com / jane123</p>
        </div>
    </div>
</body>
</html>