<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    header('Location: client/dashboard.php');
    exit();
}

if (isEmployeeLoggedIn()) {
    header('Location: employee/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">🏦 Banking System</div>
            <div class="nav-links">
                <a href="auth/login.php">Client Login</a>
                <a href="auth/employee_login.php">Employee Login</a>
                <a href="auth/register.php">Client Register</a>
            </div>
        </div>
        
        <div class="card">
            <h1>Welcome to Online Banking</h1>
            <p style="margin: 20px 0; color: #4a5568;">
                Manage your finances securely with our online banking system. 
                Clients can view their accounts and balances, while employees 
                can manage client transactions.
            </p>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>For Clients</h3>
                    <p>View your account details, check balance, and monitor transactions.</p>
                    <div style="margin-top: 20px;">
                        <a href="auth/login.php" class="btn btn-primary">Client Login</a>
                        <a href="auth/register.php" class="btn">Register</a>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <h3>For Employees</h3>
                    <p>Manage client accounts, process transactions, and view all accounts.</p>
                    <div style="margin-top: 20px;">
                        <a href="auth/employee_login.php" class="btn btn-primary">Employee Login</a>
                        <a href="auth/employee_register.php" class="btn">Employee Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>