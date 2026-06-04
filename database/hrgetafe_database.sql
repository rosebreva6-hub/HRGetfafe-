-- HRGetafe: Human Resources Information System for Getafe LGU
-- Complete Database Schema
-- Created: June 2026

-- ===================================================
-- DATABASE CREATION
-- ===================================================
CREATE DATABASE IF NOT EXISTS hrgetafe_db;
USE hrgetafe_db;

-- ===================================================
-- TABLE 1: USERS (Login System)
-- ===================================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'hr_staff', 'employee') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
);

-- ===================================================
-- TABLE 2: EMPLOYEES (Main Employee Records)
-- ===================================================
CREATE TABLE employees (
    employee_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female') NOT NULL,
    civil_status ENUM('Single', 'Married', 'Widowed', 'Divorced') NOT NULL,
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    hire_date DATE NOT NULL,
    salary DECIMAL(12, 2) NOT NULL,
    address VARCHAR(255),
    city VARCHAR(100),
    province VARCHAR(100),
    zip_code VARCHAR(10),
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    tin_number VARCHAR(20) UNIQUE,
    philhealth_number VARCHAR(20),
    sss_number VARCHAR(20),
    pagibig_number VARCHAR(20),
    status ENUM('active', 'on_leave', 'resigned', 'retired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_department (department),
    INDEX idx_position (position),
    INDEX idx_hire_date (hire_date),
    INDEX idx_status (status)
);

-- ===================================================
-- TABLE 3: ATTENDANCE (Clock In/Out Records)
-- ===================================================
CREATE TABLE attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    time_in TIME,
    time_out TIME,
    hours_worked DECIMAL(5, 2),
    minutes_late INT DEFAULT 0,
    is_absent BOOLEAN DEFAULT FALSE,
    remarks VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    UNIQUE KEY unique_attendance (employee_id, attendance_date),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_employee_date (employee_id, attendance_date)
);

-- ===================================================
-- TABLE 4: LEAVE_TYPES (Types of Leave Available)
-- ===================================================
CREATE TABLE leave_types (
    leave_type_id INT PRIMARY KEY AUTO_INCREMENT,
    leave_name VARCHAR(100) NOT NULL,
    days_per_year INT NOT NULL,
    description VARCHAR(255),
    is_paid BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_leave_name (leave_name)
);

-- ===================================================
-- TABLE 5: LEAVE_BALANCES (Remaining Leave Days Per Employee)
-- ===================================================
CREATE TABLE leave_balances (
    balance_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    year INT NOT NULL,
    total_days INT NOT NULL,
    used_days INT DEFAULT 0,
    remaining_days INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(leave_type_id),
    UNIQUE KEY unique_balance (employee_id, leave_type_id, year),
    INDEX idx_employee_year (employee_id, year)
);

-- ===================================================
-- TABLE 6: LEAVE_APPLICATIONS (Leave Request Forms)
-- ===================================================
CREATE TABLE leave_applications (
    leave_application_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    number_of_days INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT,
    approval_date DATETIME,
    rejection_reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(leave_type_id),
    FOREIGN KEY (approved_by) REFERENCES users(user_id),
    INDEX idx_status (status),
    INDEX idx_employee_id (employee_id),
    INDEX idx_start_date (start_date)
);

-- ===================================================
-- TABLE 7: PAYROLL (Monthly Salary Records)
-- ===================================================
CREATE TABLE payroll (
    payroll_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    pay_period_start DATE NOT NULL,
    pay_period_end DATE NOT NULL,
    gross_salary DECIMAL(12, 2) NOT NULL,
    sss_deduction DECIMAL(12, 2) DEFAULT 0,
    philhealth_deduction DECIMAL(12, 2) DEFAULT 0,
    pagibig_deduction DECIMAL(12, 2) DEFAULT 0,
    tax_deduction DECIMAL(12, 2) DEFAULT 0,
    other_deductions DECIMAL(12, 2) DEFAULT 0,
    total_deductions DECIMAL(12, 2) NOT NULL,
    net_salary DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'processed', 'released') DEFAULT 'pending',
    release_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    INDEX idx_pay_period (pay_period_start, pay_period_end),
    INDEX idx_employee_period (employee_id, pay_period_start)
);

-- ===================================================
-- TABLE 8: AUDIT_LOG (Track System Changes)
-- ===================================================
CREATE TABLE audit_log (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- ===================================================
-- SAMPLE DATA - LEAVE TYPES
-- ===================================================
INSERT INTO leave_types (leave_name, days_per_year, description, is_paid) VALUES
('Sick Leave', 10, 'Leave due to illness or medical appointment', TRUE),
('Vacation Leave', 15, 'Annual vacation or personal rest days', TRUE),
('Maternity Leave', 60, 'Leave for pregnant employees', TRUE),
('Paternity Leave', 7, 'Leave for fathers after child birth', TRUE),
('Study Leave', 5, 'Leave for educational purposes', FALSE),
('Emergency Leave', 3, 'Leave for emergency situations', TRUE);

-- ===================================================
-- SAMPLE DATA - ADMIN USER
-- ===================================================
INSERT INTO users (username, password, email, role, status) VALUES
('admin', SHA2('admin123', 256), 'admin@hrgetafe.gov.ph', 'admin', 'active');

-- ===================================================
-- INDEXES FOR PERFORMANCE
-- ===================================================
CREATE INDEX idx_attendance_month ON attendance(YEAR(attendance_date), MONTH(attendance_date));
CREATE INDEX idx_leave_app_employee ON leave_applications(employee_id, created_at);
CREATE INDEX idx_payroll_month ON payroll(YEAR(pay_period_start), MONTH(pay_period_start));
