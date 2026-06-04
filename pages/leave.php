<?php
include '../config/db.php';
include '../config/session.php';

requireLogin();

$user = getCurrentUser();
$success = '';
$error = '';

// Get leave applications
if ($user['role'] == 'hr_staff' || $user['role'] == 'admin') {
    $result = $conn->query("SELECT la.*, e.first_name, e.last_name, lt.leave_name FROM leave_applications la JOIN employees e ON la.employee_id = e.employee_id JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id ORDER BY la.created_at DESC");
} else {
    $employee = $conn->query("SELECT employee_id FROM employees WHERE user_id = {$user['user_id']}");
    $emp = $employee->fetch_assoc();
    $employee_id = $emp['employee_id'];
    $result = $conn->query("SELECT la.*, e.first_name, e.last_name, lt.leave_name FROM leave_applications la JOIN employees e ON la.employee_id = e.employee_id JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id WHERE la.employee_id = $employee_id ORDER BY la.created_at DESC");
}

$leave_apps = [];
while ($row = $result->fetch_assoc()) {
    $leave_apps[] = $row;
}

// Handle Approve/Reject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($user['role'] == 'hr_staff' || $user['role'] == 'admin') {
        $app_id = $_POST['application_id'];
        $action = $_POST['action'];
        
        if ($action == 'approve') {
            $sql = "UPDATE leave_applications SET status='approved', approved_by={$user['user_id']}, approval_date=NOW() WHERE leave_application_id=$app_id";
            if ($conn->query($sql)) {
                $success = 'Leave application approved!';
            }
        } elseif ($action == 'reject') {
            $reason = trim($_POST['rejection_reason']);
            $sql = "UPDATE leave_applications SET status='rejected', rejection_reason='$reason', approval_date=NOW() WHERE leave_application_id=$app_id";
            if ($conn->query($sql)) {
                $success = 'Leave application rejected!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave Management - HRGetafe</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #333;
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
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .leave-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
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
            <h2>📝 Leave Management</h2>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php foreach ($leave_apps as $app): ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <h3><?php echo $app['first_name'] . ' ' . $app['last_name']; ?></h3>
                    <p style="color: #666; font-size: 14px;"><?php echo $app['leave_name']; ?> Leave</p>
                </div>
                <span class="leave-status status-<?php echo strtolower($app['status']); ?>"><?php echo ucfirst($app['status']); ?></span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                <div>
                    <strong>From:</strong> <?php echo date('M d, Y', strtotime($app['start_date'])); ?>
                </div>
                <div>
                    <strong>To:</strong> <?php echo date('M d, Y', strtotime($app['end_date'])); ?>
                </div>
                <div>
                    <strong>Days:</strong> <?php echo $app['number_of_days']; ?> day(s)
                </div>
                <div>
                    <strong>Applied:</strong> <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                </div>
            </div>
            
            <div style="margin-bottom: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                <strong>Reason:</strong>
                <p style="color: #666; margin-top: 5px;"><?php echo $app['reason']; ?></p>
            </div>
            
            <?php if ($app['status'] == 'pending' && ($user['role'] == 'hr_staff' || $user['role'] == 'admin')): ?>
            <div style="display: flex; gap: 10px;">
                <form method="POST" action="" style="flex: 1;">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="application_id" value="<?php echo $app['leave_application_id']; ?>">
                    <button type="submit" class="btn btn-success" style="width: 100%;">✓ Approve</button>
                </form>
                
                <form method="POST" action="" style="flex: 1;">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="application_id" value="<?php echo $app['leave_application_id']; ?>">
                    <input type="hidden" name="rejection_reason" value="Rejected">
                    <button type="submit" class="btn btn-danger" style="width: 100%;">✗ Reject</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>