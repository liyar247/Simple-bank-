<?php
require_once '../includes/config.php';

if (isLoggedIn()) {
    header('Location: ../client/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_number = trim($_POST['account_number']);
    $password = $_POST['password'];
    
    if (empty($account_number) || empty($password)) {
        $error = 'Account number and password are required!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE account_number = ?");
        $stmt->execute([$account_number]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($client && password_verify($password, $client['password'])) {
            $_SESSION['user_id'] = $client['id'];
            $_SESSION['account_number'] = $client['account_number'];
            $_SESSION['full_name'] = $client['full_name'];
            header('Location: ../client/dashboard.php');
            exit();
        } else {
            $error = 'Invalid account number or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - Banking System</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 0 auto;">
            <h2 style="text-align: center; margin-bottom: 30px;">Client Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Account Number</label>
                    <input type="text" name="account_number" class="form-control" required 
                           value="<?php echo isset($_POST['account_number']) ? htmlspecialchars($_POST['account_number']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p>Are you an employee? <a href="employee_login.php">Employee Login</a></p>
                <p><a href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>