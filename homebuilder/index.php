<?php
session_start();
include("includes/config.php");
include("includes/functions.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch building details for the user
$user_id = $_SESSION['user_id'];
$building_details = getBuildingDetailsByUserId($user_id);

// Redirect to building details page if not completed
if (!$building_details) {
    header("Location: building_details.php");
    exit();
}

// Get remaining budget
$remaining_budget = getRemainingBudget($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Builder - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="welcome-banner">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>Your dream home is a few steps away. Let's make it happen together.</p>
        </div>
        
        <div class="budget-display">
            <div class="budget-card">
                <h3>Available Budget</h3>
                <p class="budget-amount">₹<?php echo number_format($remaining_budget); ?></p>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <a href="building_plans.php" class="dashboard-card">
                <i class="fas fa-home"></i>
                <h3>Building Plans</h3>
                <p>Explore design options based on your requirements</p>
            </a>
            
            <a href="marketplace.php" class="dashboard-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>Marketplace</h3>
                <p>Find materials and supplies within your budget</p>
            </a>
            
            <a href="forum.php" class="dashboard-card">
                <i class="fas fa-comments"></i>
                <h3>Community Forum</h3>
                <p>Connect with others and share experiences</p>
            </a>
            
            <a href="roadmap.php" class="dashboard-card">
                <i class="fas fa-map"></i>
                <h3>Construction Roadmap</h3>
                <p>Follow the step-by-step guide to build your home</p>
            </a>
            
            <a href="progress_log.php" class="dashboard-card">
                <i class="fas fa-tasks"></i>
                <h3>Progress Log</h3>
                <p>Track your expenses and construction progress</p>
            </a>
            
            <a href="workers.php" class="dashboard-card">
                <i class="fas fa-hard-hat"></i>
                <h3>Workers</h3>
                <p>Find and hire reliable construction workers</p>
            </a>
            
            <a href="permits.php" class="dashboard-card">
                <i class="fas fa-file-contract"></i>
                <h3>Legal Permits</h3>
                <p>Navigate legal requirements for your location</p>
            </a>
        </div>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <script src="js/script.js"></script>
</body>
</html>