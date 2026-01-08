<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Get client details
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE account_number = ? ORDER BY transaction_date DESC LIMIT 10");
$stmt->execute([$client['account_number']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Banking System</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">👤 Client Dashboard</div>
            <div class="nav-links">
                <span>Welcome, <?php echo htmlspecialchars($client['full_name']); ?></span>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="card">
            <h2>Account Overview</h2>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Account Number</h3>
                    <p><?php echo $client['account_number']; ?></p>
                </div>
                
                <div class="dashboard-card">
                    <h3>Current Balance</h3>
                    <p>$<?php echo number_format($client['balance'], 2); ?></p>
                </div>
                
                <div class="dashboard-card">
                    <h3>Account Created</h3>
                    <p><?php echo date('F d, Y', strtotime($client['created_at'])); ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>Recent Transactions</h2>
            
            <?php if (empty($transactions)): ?>
                <p style="text-align: center; color: #718096; padding: 20px;">
                    No transactions found.
                </p>
            <?php else: ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo date('M d, Y h:i A', strtotime($transaction['transaction_date'])); ?></td>
                            <td>
                                <span class="<?php echo $transaction['type']; ?>">
                                    <?php echo ucfirst($transaction['type']); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Account Information</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($client['full_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($client['email']); ?></p>
                    <p><strong>Account Status:</strong> <span style="color: #48bb78;">Active</span></p>
                </div>
                <div>
                    <p><strong>Total Transactions:</strong> <?php echo count($transactions); ?></p>
                    <p><strong>Last Login:</strong> Today</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>