<?php
include '../config/db.php';
include '../config/session.php';

requireLogin();

$user = getCurrentUser();
$success = '';
$error = '';

// Get current month attendance data
$current_month = date('Y-m');
$result = $conn->query("SELECT a.*, e.first_name, e.last_name FROM attendance a JOIN employees e ON a.employee_id = e.employee_id WHERE DATE_FORMAT(a.attendance_date, '%Y-%m') = '$current_month' ORDER BY a.attendance_date DESC");
$attendance_records = [];
while ($row = $result->fetch_assoc()) {
    $attendance_records[] = $row;
}

// Handle Clock In/Out for employees
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($user['role'] == 'employee') {
        // Get employee_id from session or user table
        $user_query = $conn->query("SELECT employee_id FROM employees WHERE user_id = {$user['user_id']}");
        if ($user_query->num_rows > 0) {
            $emp = $user_query->fetch_assoc();
            $employee_id = $emp['employee_id'];
            
            $today = date('Y-m-d');
            $current_time = date('H:i:s');
            
            if ($_POST['action'] == 'clock_in') {
                // Check if already clocked in
                $check = $conn->query("SELECT * FROM attendance WHERE employee_id = $employee_id AND attendance_date = '$today'");
                if ($check->num_rows == 0) {
                    $sql = "INSERT INTO attendance (employee_id, attendance_date, time_in) VALUES ($employee_id, '$today', '$current_time')";
                    if ($conn->query($sql)) {
                        $success = 'Clocked in successfully at ' . $current_time;
                    } else {
                        $error = 'Error clocking in';
                    }
                } else {
                    $error = 'You have already clocked in today';
                }
            } elseif ($_POST['action'] == 'clock_out') {
                $sql = "UPDATE attendance SET time_out = '$current_time' WHERE employee_id = $employee_id AND attendance_date = '$today'";
                if ($conn->query($sql)) {
                    $success = 'Clocked out successfully at ' . $current_time;
                } else {
                    $error = 'Error clocking out';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance - HRGetafe</title>
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
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: #764ba2;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .clock-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .current-time {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏢 HRGetafe</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="header">
            <h2>📅 Attendance System</h2>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($user['role'] == 'employee'): ?>
        <div class="clock-card">
            <h3>Employee Clock In/Out</h3>
            <div class="current-time" id="currentTime">Loading...</div>
            
            <form method="POST" action="" style="margin-top: 20px;">
                <button type="submit" name="action" value="clock_in" class="btn btn-success">⏱️ Clock In</button>
                <button type="submit" name="action" value="clock_out" class="btn btn-danger">⏹️ Clock Out</button>
            </form>
        </div>
        <?php endif; ?>
        
        <h3 style="margin-bottom: 15px; color: #333;">Attendance Records - <?php echo date('F Y'); ?></h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee Name</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours Worked</th>
                        <th>Minutes Late</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
                        <td><?php echo $record['first_name'] . ' ' . $record['last_name']; ?></td>
                        <td><?php echo $record['time_in'] ? date('h:i A', strtotime($record['time_in'])) : '-'; ?></td>
                        <td><?php echo $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : '-'; ?></td>
                        <td><?php echo $record['hours_worked'] ? number_format($record['hours_worked'], 2) : '-'; ?></td>
                        <td><?php echo $record['minutes_late'] > 0 ? $record['minutes_late'] . ' mins' : '-'; ?></td>
                        <td><?php echo $record['is_absent'] ? '❌ Absent' : '✅ Present'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // Update current time display
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>