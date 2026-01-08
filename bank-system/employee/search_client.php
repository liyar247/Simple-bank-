<?php
require_once '../includes/config.php';

if (!isEmployeeLoggedIn()) {
    header('Location: ../auth/employee_login.php');
    exit();
}

$client = null;
$transactions = [];
$error = '';
$success = '';

// Handle client search
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_type = $_POST['search_type'] ?? 'account_number';
    $search_value = trim($_POST['search_value']);
    
    if (empty($search_value)) {
        $error = 'Please enter a search value!';
    } else {
        try {
            if ($search_type === 'account_number') {
                // Search by account number
                $stmt = $pdo->prepare("SELECT * FROM clients WHERE account_number = ?");
                $stmt->execute([$search_value]);
                $client = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$client) {
                    $error = 'Client not found with account number: ' . htmlspecialchars($search_value);
                } else {
                    // Get recent transactions for this client
                    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE account_number = ? ORDER BY transaction_date DESC LIMIT 10");
                    $stmt->execute([$client['account_number']]);
                    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $success = 'Client found!';
                }
                
            } elseif ($search_type === 'name') {
                // Search by name (partial match)
                $stmt = $pdo->prepare("SELECT * FROM clients WHERE full_name LIKE ? LIMIT 10");
                $stmt->execute(["%$search_value%"]);
                $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($search_results)) {
                    $error = 'No clients found with name containing: ' . htmlspecialchars($search_value);
                } elseif (count($search_results) === 1) {
                    // If only one result, show directly
                    $client = $search_results[0];
                    
                    // Get recent transactions for this client
                    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE account_number = ? ORDER BY transaction_date DESC LIMIT 10");
                    $stmt->execute([$client['account_number']]);
                    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $success = 'Client found!';
                } else {
                    // Multiple results, show list
                    $multiple_results = $search_results;
                }
            } elseif ($search_type === 'email') {
                // Search by email
                $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
                $stmt->execute([$search_value]);
                $client = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$client) {
                    $error = 'Client not found with email: ' . htmlspecialchars($search_value);
                } else {
                    // Get recent transactions for this client
                    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE account_number = ? ORDER BY transaction_date DESC LIMIT 10");
                    $stmt->execute([$client['account_number']]);
                    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $success = 'Client found!';
                }
            }
        } catch(Exception $e) {
            $error = 'Search failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Client - Banking System</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .search-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-option {
            flex: 1;
            text-align: center;
        }
        
        .search-option input[type="radio"] {
            display: none;
        }
        
        .search-option label {
            display: block;
            padding: 12px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-option input[type="radio"]:checked + label {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .search-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .client-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .action-buttons .btn {
            flex: 1;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .info-item {
            padding: 15px;
            background: #f7fafc;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">🔍 Search Client</div>
            <div class="nav-links">
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="credit.php" class="btn btn-success">Credit</a>
                <a href="debit.php" class="btn btn-danger">Debit</a>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="card">
            <h2>Search Client</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="searchForm">
                <div class="search-options">
                    <div class="search-option">
                        <input type="radio" id="search_account" name="search_type" value="account_number" 
                               <?php echo (!isset($_POST['search_type']) || $_POST['search_type'] === 'account_number') ? 'checked' : ''; ?>>
                        <label for="search_account">By Account Number</label>
                    </div>
                    <div class="search-option">
                        <input type="radio" id="search_name" name="search_type" value="name"
                               <?php echo (isset($_POST['search_type']) && $_POST['search_type'] === 'name') ? 'checked' : ''; ?>>
                        <label for="search_name">By Name</label>
                    </div>
                    <div class="search-option">
                        <input type="radio" id="search_email" name="search_type" value="email"
                               <?php echo (isset($_POST['search_type']) && $_POST['search_type'] === 'email') ? 'checked' : ''; ?>>
                        <label for="search_email">By Email</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div id="searchPlaceholder">
                        <input type="text" name="search_value" class="form-control" required 
                               placeholder="Enter account number to search"
                               value="<?php echo isset($_POST['search_value']) ? htmlspecialchars($_POST['search_value']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        🔍 Search Client
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (isset($multiple_results)): ?>
        <div class="card">
            <h3>Multiple Clients Found (<?php echo count($multiple_results); ?>)</h3>
            <p>Click on a client to view details:</p>
            
            <div class="search-results-grid">
                <?php foreach ($multiple_results as $result): ?>
                <div class="client-card" onclick="selectClient('<?php echo $result['account_number']; ?>')">
                    <h4><?php echo htmlspecialchars($result['full_name']); ?></h4>
                    <p><strong>Account:</strong> <?php echo $result['account_number']; ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($result['email']); ?></p>
                    <p><strong>Balance:</strong> $<?php echo number_format($result['balance'], 2); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <script>
        function selectClient(accountNumber) {
            document.querySelector('input[name="search_type"]').value = 'account_number';
            document.querySelector('input[name="search_value"]').value = accountNumber;
            document.getElementById('searchForm').submit();
        }
        </script>
        <?php endif; ?>
        
        <?php if ($client): ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Client Details</h3>
                <div style="display: flex; gap: 10px;">
                    <span class="btn" style="background: #667eea; color: white; padding: 8px 16px; border-radius: 5px;">
                        Account: <?php echo $client['account_number']; ?>
                    </span>
                    <button onclick="copyToClipboard('<?php echo $client['account_number']; ?>')" 
                            class="btn" style="background: #718096; color: white;">
                        Copy
                    </button>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($client['full_name']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($client['email']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Current Balance</div>
                    <div class="info-value" style="color: #48bb78; font-weight: bold;">
                        $<?php echo number_format($client['balance'], 2); ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Account Created</div>
                    <div class="info-value"><?php echo date('F d, Y', strtotime($client['created_at'])); ?></div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="credit.php?account=<?php echo urlencode($client['account_number']); ?>" 
                   class="btn btn-success">💳 Credit Amount</a>
                <a href="debit.php?account=<?php echo urlencode($client['account_number']); ?>" 
                   class="btn btn-danger">💸 Debit Amount</a>
                <a href="search_client.php" class="btn">🔄 New Search</a>
            </div>
        </div>
        
        <?php if (!empty($transactions)): ?>
        <div class="card">
            <h3>Recent Transactions (Last 10)</h3>
            
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Employee ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo date('M d, Y h:i A', strtotime($transaction['transaction_date'])); ?></td>
                        <td>
                            <span class="<?php echo $transaction['type']; ?>" 
                                  style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                <?php echo strtoupper($transaction['type']); ?>
                            </span>
                        </td>
                        <td style="font-weight: <?php echo $transaction['type'] === 'credit' ? 'bold' : 'normal'; ?>">
                            <?php echo ($transaction['type'] === 'credit' ? '+' : '-'); ?>
                            $<?php echo number_format($transaction['amount'], 2); ?>
                        </td>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td>#<?php echo $transaction['employee_id']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="card">
            <h3>Transaction History</h3>
            <p style="text-align: center; color: #718096; padding: 20px;">
                No transactions found for this client.
            </p>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
    // Update placeholder based on search type
    const searchTypeRadios = document.querySelectorAll('input[name="search_type"]');
    const searchInput = document.querySelector('input[name="search_value"]');
    
    searchTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const placeholderMap = {
                'account_number': 'Enter account number (e.g., BNK202312012345)',
                'name': 'Enter client name or part of name',
                'email': 'Enter email address'
            };
            
            searchInput.placeholder = placeholderMap[this.value];
            
            // Clear input on type change
            searchInput.value = '';
            searchInput.focus();
        });
    });
    
    // Copy to clipboard function
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Account number copied to clipboard!', 'success');
        }).catch(err => {
            showAlert('Failed to copy: ' + err, 'error');
        });
    }
    
    // Show alert function
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            alertDiv.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alertDiv.remove(), 500);
        }, 3000);
    }
    
    // Add CSS for animation
    if (!document.querySelector('#alert-styles')) {
        const style = document.createElement('style');
        style.id = 'alert-styles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Auto-focus search input on page load
    document.addEventListener('DOMContentLoaded', function() {
        searchInput.focus();
        
        // Check if there's a URL parameter for account
        const urlParams = new URLSearchParams(window.location.search);
        const accountParam = urlParams.get('account');
        
        if (accountParam) {
            document.querySelector('input[name="search_type"][value="account_number"]').checked = true;
            document.querySelector('input[name="search_value"]').value = accountParam;
            document.getElementById('searchForm').submit();
        }
    });
    </script>
</body>
</html>