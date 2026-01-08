<?php
require_once '../includes/config.php';

if (!isEmployeeLoggedIn()) {
    header('Location: ../auth/employee_login.php');
    exit();
}

// Get total clients count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
$total_clients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total balance
$stmt = $pdo->query("SELECT SUM(balance) as total_balance FROM clients");
$total_balance = $stmt->fetch(PDO::FETCH_ASSOC)['total_balance'];

// Get recent transactions
$stmt = $pdo->query("SELECT t.*, c.full_name FROM transactions t 
                     JOIN clients c ON t.account_number = c.account_number 
                     ORDER BY t.transaction_date DESC LIMIT 10");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Banking System</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">👨‍💼 Employee Dashboard</div>
            <div class="nav-links">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['employee_name']); ?></span>
                <a href="search_client.php" class="btn">Search Client</a>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="card">
            <h2>Bank Overview</h2>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Total Clients</h3>
                    <p><?php echo $total_clients; ?></p>
                </div>
                
                <div class="dashboard-card">
                    <h3>Total Balance</h3>
                    <p>$<?php echo number_format($total_balance, 2); ?></p>
                </div>
                
                <div class="dashboard-card">
                    <h3>Your Actions</h3>
                    <div style="margin-top: 15px;">
                        <a href="search_client.php" class="btn btn-primary" style="margin-right: 10px;">Search Client</a>
                        <a href="credit.php" class="btn btn-success" style="margin-right: 10px;">Credit</a>
                        <a href="debit.php" class="btn btn-danger">Debit</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>Recent Bank Transactions</h2>
            
            <?php if (empty($transactions)): ?>
                <p style="text-align: center; color: #718096; padding: 20px;">
                    No transactions found.
                </p>
            <?php else: ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Client Name</th>
                            <th>Account No.</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo date('M d, Y h:i A', strtotime($transaction['transaction_date'])); ?></td>
                            <td><?php echo htmlspecialchars($transaction['full_name']); ?></td>
                            <td><?php echo $transaction['account_number']; ?></td>
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
    </div>
</body>
</html>