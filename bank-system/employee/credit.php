<?php
require_once '../includes/config.php';

if (!isEmployeeLoggedIn()) {
    header('Location: ../auth/employee_login.php');
    exit();
}

$error = '';
$success = '';
$client = null;

// First, search for client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $account_number = trim($_POST['account_number']);
    
    if (empty($account_number)) {
        $error = 'Please enter an account number!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE account_number = ?");
        $stmt->execute([$account_number]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$client) {
            $error = 'Client not found!';
        }
    }
}

// Process credit transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credit'])) {
    $account_number = $_POST['account_number'];
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    
    if ($amount <= 0) {
        $error = 'Amount must be greater than 0!';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update client balance
            $stmt = $pdo->prepare("UPDATE clients SET balance = balance + ? WHERE account_number = ?");
            $stmt->execute([$amount, $account_number]);
            
            // Record transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (account_number, type, amount, description, employee_id) VALUES (?, 'credit', ?, ?, ?)");
            $stmt->execute([$account_number, $amount, $description, $_SESSION['employee_id']]);
            
            $pdo->commit();
            $success = "Successfully credited $".number_format($amount, 2)." to account $account_number";
            $client = null; // Clear client to allow new search
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = 'Transaction failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Amount - Banking System</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">💰 Credit Amount</div>
            <div class="nav-links">
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="search_client.php" class="btn">Search Client</a>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="card">
            <h2>Credit Amount to Client Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (!$client): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Enter Client Account Number</label>
                    <input type="text" name="account_number" class="form-control" required 
                           placeholder="Enter account number to search"
                           value="<?php echo isset($_POST['account_number']) ? htmlspecialchars($_POST['account_number']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" name="search" class="btn btn-primary" style="width: 100%;">Search Client</button>
                </div>
            </form>
            <?php else: ?>
            <div style="margin-bottom: 30px;">
                <h3>Client Details</h3>
                <p><strong>Account Number:</strong> <?php echo $client['account_number']; ?></p>
                <p><strong>Client Name:</strong> <?php echo htmlspecialchars($client['full_name']); ?></p>
                <p><strong>Current Balance:</strong> $<?php echo number_format($client['balance'], 2); ?></p>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="account_number" value="<?php echo $client['account_number']; ?>">
                
                <div class="form-group">
                    <label>Amount to Credit ($)</label>
                    <input type="number" name="amount" class="form-control" required 
                           min="0.01" step="0.01" placeholder="Enter amount">
                </div>
                
                <div class="form-group">
                    <label>Description (Optional)</label>
                    <input type="text" name="description" class="form-control" 
                           placeholder="Enter description for this transaction">
                </div>
                
                <div class="form-group">
                    <button type="submit" name="credit" class="btn btn-success" style="width: 100%;">Credit Amount</button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="credit.php" class="btn">Search Another Client</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>