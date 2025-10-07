<?php
if (!isset($remaining_budget)) {
    $remaining_budget = isset($remaining_budget) ? $remaining_budget : (isset($_SESSION['user_id']) ? getRemainingBudget($_SESSION['user_id']) : 0);
}
?>

<header class="site-header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php">
                <i class="fas fa-home"></i>
                <span>Home Builder</span>
            </a>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="building_plans.php">Plans</a></li>
                    <li><a href="marketplace.php">Marketplace</a></li>
                    <li><a href="workers.php">Workers</a></li>
                    <li><a href="progress_log.php">Progress</a></li>
                    <li><a href="forum.php">Community</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="budget-display-mini">
                    <i class="fas fa-wallet"></i>
                    <span>₹<?php echo number_format($remaining_budget); ?></span>
                </div>
                
                <div class="user-menu">
                    <button class="user-menu-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="dropdown-menu">
                        <a href="building_details.php">Update Building Details</a>
                        <a href="logout.php">Logout</a>
                        <a href="delete_account.php">Delete Account</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>