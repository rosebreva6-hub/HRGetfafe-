<?php
include '../config/db.php';
include '../config/session.php';

requireLogin();
requireRole('hr_staff');

$user = getCurrentUser();
$error = '';
$success = '';

// Handle Add Employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $position = trim($_POST['position']);
    $department = trim($_POST['department']);
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $zip_code = trim($_POST['zip_code']);
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'Please fill in all required fields!';
    } else {
        $sql = "INSERT INTO employees (first_name, middle_name, last_name, email, phone, date_of_birth, gender, civil_status, position, department, hire_date, salary, address, city, province, zip_code) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssss", $first_name, $middle_name, $last_name, $email, $phone, $date_of_birth, $gender, $civil_status, $position, $department, $hire_date, $salary, $address, $city, $province, $zip_code);
        
        if ($stmt->execute()) {
            $success = 'Employee added successfully!';
        } else {
            $error = 'Error adding employee: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Get all employees
$result = $conn->query("SELECT * FROM employees WHERE status='active' ORDER BY last_name, first_name");
$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employees - HRGetafe</title>
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
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
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
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal.show {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        
        .modal-header h3 {
            color: #333;
        }
        
        .close {
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.1);
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
            <h2>Employee Records</h2>
            <button class="btn" onclick="showAddModal()">+ Add New Employee</button>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Hire Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><?php echo $emp['employee_id']; ?></td>
                        <td><?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?></td>
                        <td><?php echo $emp['position']; ?></td>
                        <td><?php echo $emp['department']; ?></td>
                        <td><?php echo $emp['email']; ?></td>
                        <td><?php echo $emp['phone']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($emp['hire_date'])); ?></td>
                        <td>
                            <button class="btn" onclick="alert('Edit feature coming soon')">Edit</button>
                            <button class="btn btn-danger" onclick="alert('Delete feature coming soon')">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Employee Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Employee</h3>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name">
                </div>
                
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
                
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth">
                </div>
                
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Civil Status</label>
                    <select name="civil_status">
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Divorced">Divorced</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Position *</label>
                    <input type="text" name="position" required>
                </div>
                
                <div class="form-group">
                    <label>Department *</label>
                    <input type="text" name="department" required>
                </div>
                
                <div class="form-group">
                    <label>Hire Date *</label>
                    <input type="date" name="hire_date" required>
                </div>
                
                <div class="form-group">
                    <label>Salary *</label>
                    <input type="number" name="salary" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city">
                </div>
                
                <div class="form-group">
                    <label>Province</label>
                    <input type="text" name="province">
                </div>
                
                <div class="form-group">
                    <label>Zip Code</label>
                    <input type="text" name="zip_code">
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">Add Employee</button>
            </form>
        </div>
    </div>
    
    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.add('show');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('addModal');
            if (event.target == modal) {
                modal.classList.remove('show');
            }
        }
    </script>
</body>
</html>