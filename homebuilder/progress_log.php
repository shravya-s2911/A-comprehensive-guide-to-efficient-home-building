<?php
session_start();
include("includes/config.php");
include("includes/functions.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Fetch building details for the user
$building_details = getBuildingDetailsByUserId($user_id);

// Redirect to building details page if not completed
if (!$building_details) {
    header("Location: building_details.php");
    exit();
}

// Get remaining budget
$remaining_budget = getRemainingBudget($user_id);

// Handle expense log submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_expense'])) {
    $task = $_POST['task'];
    $amount = $_POST['amount'];
    
    // Validate inputs
    if (empty($task) || empty($amount)) {
        $error = "Please fill in all fields";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a positive number";
    } else {
        // Insert expense log
        $sql = "INSERT INTO progress_logs (user_id, task, amount_spent, timestamp) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isd", $user_id, $task, $amount);
        
        if ($stmt->execute()) {
            $success = "Expense logged successfully!";
            
            // Refresh page to update logs and budget
            header("refresh:1;url=progress_log.php");
            exit();
        } else {
            $error = "Failed to log expense: " . $conn->error;
        }
    }
}

// Handle delete log entry
if (isset($_GET['delete_log']) && is_numeric($_GET['delete_log'])) {
    $log_id = $_GET['delete_log'];
    
    $sql = "DELETE FROM progress_logs WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $log_id, $user_id);
    
    if ($stmt->execute()) {
        $success = "Log entry deleted successfully!";
        
        // Refresh page to update logs and budget
        header("Location: progress_log.php");
        exit();
    } else {
        $error = "Failed to delete log entry: " . $conn->error;
    }
}

// Fetch expense logs
$logs = [];
$sql = "SELECT * FROM progress_logs WHERE user_id = ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

// Calculate expense distribution by category
$categories = [
    'Materials' => 0,
    'Labor' => 0,
    'Permits' => 0,
    'Design' => 0,
    'Other' => 0
];

$total_spent = 0;
foreach ($logs as $log) {
    $total_spent += $log['amount_spent'];
    
    // Simple categorization based on keywords in task description
    $task_lower = strtolower($log['task']);
    if (strpos($task_lower, 'material') !== false || strpos($task_lower, 'cement') !== false || 
        strpos($task_lower, 'brick') !== false || strpos($task_lower, 'sand') !== false) {
        $categories['Materials'] += $log['amount_spent'];
    } elseif (strpos($task_lower, 'labor') !== false || strpos($task_lower, 'worker') !== false || 
              strpos($task_lower, 'contractor') !== false) {
        $categories['Labor'] += $log['amount_spent'];
    } elseif (strpos($task_lower, 'permit') !== false || strpos($task_lower, 'legal') !== false || 
              strpos($task_lower, 'approval') !== false) {
        $categories['Permits'] += $log['amount_spent'];
    } elseif (strpos($task_lower, 'design') !== false || strpos($task_lower, 'plan') !== false || 
              strpos($task_lower, 'architect') !== false) {
        $categories['Design'] += $log['amount_spent'];
    } else {
        $categories['Other'] += $log['amount_spent'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Log - Home Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Progress Log</h1>
            <p>Track your construction expenses and monitor your budget</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="budget-summary">
            <div class="budget-card">
                <h3>Total Budget</h3>
                <p class="budget-amount">₹<?php echo number_format($building_details['budget']); ?></p>
            </div>
            
            <div class="budget-card">
                <h3>Amount Spent</h3>
                <p class="budget-amount spent">₹<?php echo number_format($total_spent); ?></p>
            </div>
            
            <div class="budget-card">
                <h3>Remaining Budget</h3>
                <p class="budget-amount available">₹<?php echo number_format($remaining_budget); ?></p>
            </div>
        </div>
        
        <div class="progress-grid">
            <div class="expense-form-container">
                <h2>Log New Expense</h2>
                <form method="POST" action="" class="expense-form">
                    <div class="form-group">
                        <label for="task">Expense Description</label>
                        <input type="text" id="task" name="task" placeholder="e.g., Purchased cement, Hired workers" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Amount Spent (₹)</label>
                        <input type="number" id="amount" name="amount" required>
                    </div>
                    
                    <button type="submit" name="add_expense" class="btn btn-primary">Add Expense</button>
                </form>
            </div>
            
            <div class="expense-chart-container">
                <h2>Expense Distribution</h2>
                <canvas id="expenseChart"></canvas>
            </div>
        </div>
        
        <div class="logs-container">
            <h2>Expense History</h2>
            <?php if (empty($logs)): ?>
                <p class="no-data">No expenses logged yet. Use the form above to add your first expense.</p>
            <?php else: ?>
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo date('M d, Y h:i A', strtotime($log['timestamp'])); ?></td>
                                <td><?php echo htmlspecialchars($log['task']); ?></td>
                                <td>₹<?php echo number_format($log['amount_spent']); ?></td>
                                <td>
                                    <a href="edit_log.php?id=<?php echo $log['id']; ?>" class="edit-btn" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    &nbsp;
                                    <a href="progress_log.php?delete_log=<?php echo $log['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this log entry?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <script>
        // Create expense distribution chart
        const ctx = document.getElementById('expenseChart').getContext('2d');
        const expenseChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($categories)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($categories)); ?>,
                    backgroundColor: [
                        '#4CAF50', // Materials
                        '#2196F3', // Labor
                        '#FF9800', // Permits
                        '#9C27B0', // Design
                        '#607D8B'  // Other
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ₹${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
    
    <script src="js/script.js"></script>
</body>
</html>