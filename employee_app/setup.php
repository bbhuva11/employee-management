<?php
$host = "localhost";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Create Database
$conn->query("CREATE DATABASE IF NOT EXISTS employeedatamanagement");
$conn->select_db("employeedatamanagement");

// 2. Create Designation Table
$sql_designation = "CREATE TABLE IF NOT EXISTS designation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql_designation);

// 3. Create Employee Master Table
$sql_employee = "CREATE TABLE IF NOT EXISTS employee_master (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_no VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    birthdate DATE NOT NULL,
    mobile VARCHAR(15) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    address TEXT,
    degree VARCHAR(100),
    salary DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    designation_id INT NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employee_designation 
        FOREIGN KEY (designation_id) REFERENCES designation(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE
)";
$conn->query($sql_employee);

// 4. Create Trigger for Auto Employee Number
$conn->query("DROP TRIGGER IF EXISTS generate_employee_no");
$sql_trigger = "CREATE TRIGGER generate_employee_no
BEFORE INSERT ON employee_master
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT AUTO_INCREMENT INTO next_id
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'employee_master';
    SET NEW.employee_no = CONCAT('OP', LPAD(next_id, 4, '0'));
END;";
$conn->query($sql_trigger);

// 5. Seed Initial Designations
$conn->query("INSERT IGNORE INTO designation (name) VALUES 
    ('Software Engineer'),
    ('Senior Developer'),
    ('HR Manager'),
    ('Project Manager')");

echo "<div style='font-family: Arial; padding: 20px; color: green;'>
        <h2>Database and Tables Configured Successfully!</h2>
        <a href='index.php'>Go to Add Employee Form</a>
      </div>";

$conn->close();
?>