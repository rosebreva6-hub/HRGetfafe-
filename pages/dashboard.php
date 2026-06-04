<?php
include '../config/db.php';
include '../config/session.php';

requireLogin();

$user = getCurrentUser();

// Get dashboard statistics
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status='active'")->fetch_assoc()['count'];
$today_present = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE attendance_date=CURDATE() AND is_absent=FALSE")->fetch_assoc()['count'];
$pending_leaves = $conn->query("SELECT COUNT(*) as count FROM leave_applications WHERE status='pending'")->fetch_assoc()['count'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - HRGetafe</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 15px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
            transition: background 0.3s;
        }
        
        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .welcome {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #666;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            color: #667eea;
            font-size: 32px;
            font-weight: bold;
        }
        
        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .menu-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .menu-item .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .menu-item h4 {
            font-size: 16px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏢 HRGetafe</h1>
        <div>
            <span><?php echo ucfirst($user['role']); ?> | <?php echo $user['username']; ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h2>Welcome, <?php echo $user['username']; ?>!</h2>
            <p>Human Resources Information System for Getafe LGU</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Employees</h3>
                <div class="number"><?php echo $total_employees; ?></div>
            </div>
            <div class="stat-card">
                <h3>Present Today</h3>
                <div class="number"><?php echo $today_present; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Leave Approvals</h3>
                <div class="number"><?php echo $pending_leaves; ?></div>
            </div>
        </div>
        
        <h3 style="margin-bottom: 20px; color: #333;">Quick Menu</h3>
        <div class="menu">
            <a href="employees.php" class="menu-item">
                <div class="icon">👥</div>
                <h4>Employees</h4>
            </a>
            <a href="attendance.php" class="menu-item">
                <div class="icon">📅</div>
                <h4>Attendance</h4>
            </a>
            <a href="leave.php" class="menu-item">
                <div class="icon">📝</div>
                <h4>Leave Requests</h4>
            </a>
            <a href="payroll.php" class="menu-item">
                <div class="icon">💰</div>
                <h4>Payroll</h4>
            </a>
            <a href="reports.php" class="menu-item">
                <div class="icon">📊</div>
                <h4>Reports</h4>
            </a>
        </div>
    </div>
</body>
</html>