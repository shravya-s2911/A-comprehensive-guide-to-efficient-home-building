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

// Get the log ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: progress_log.php");
    exit();
}

$log_id = (int)$_GET['id'];

// Fetch the existing log entry for the user
$sql = "SELECT * FROM progress_logs WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $log_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Log not found or doesn't belong to user
    header("Location: progress_log.php");
    exit();
}

$log = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_expense'])) {
    $task = trim($_POST['task']);
    $amount = trim($_POST['amount']);
    
    // Validate inputs
    if (empty($task) || empty($amount)) {
        $error = "Please fill in all fields";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a positive number";
    } else {
        // Update the log entry
        $sql = "UPDATE progress_logs SET task = ?, amount_spent = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdii", $task, $amount, $log_id, $user_id);
        
        if ($stmt->execute()) {
            $success = "Expense log updated successfully!";
            // Redirect back to progress log after 1 second
            header("refresh:1;url=progress_log.php");
            exit();
        } else {
            $error = "Failed to update log: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Expense Log - Home Builder</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <?php include("includes/header.php"); ?>

    <div class="container">
        <div class="page-header">
            <h1>Edit Expense Log</h1>
            <p>Modify the details of your construction expense</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="expense-form">
            <div class="form-group">
                <label for="task">Expense Description</label>
                <input
                    type="text"
                    id="task"
                    name="task"
                    value="<?php echo htmlspecialchars($log['task']); ?>"
                    required
                />
            </div>

            <div class="form-group">
                <label for="amount">Amount Spent (₹)</label>
                <input
                    type="number"
                    step="0.01"
                    id="amount"
                    name="amount"
                    value="<?php echo htmlspecialchars($log['amount_spent']); ?>"
                    required
                />
            </div>

            <button type="submit" name="update_expense" class="btn btn-primary">Update Expense</button>
            <a href="progress_log.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>

    <?php include("includes/footer.php"); ?>
</body>
</html>
