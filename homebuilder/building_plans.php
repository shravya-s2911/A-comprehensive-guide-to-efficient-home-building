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

// Fetch building details for the user
$building_details = getBuildingDetailsByUserId($user_id);

// Redirect to building details page if not completed
if (!$building_details) {
    header("Location: building_details.php");
    exit();
}

// Get remaining budget
$remaining_budget = getRemainingBudget($user_id);

// Fetch building plans based on user's building type
$building_type = $building_details['building_type'];
$plot_area = $building_details['plot_area'];
$budget = $building_details['budget'];
$floors = $building_details['floors'];

// Calculate approximate costs based on building details
$cost_per_sqft = 0;
switch ($building_type) {
    case 'residential':
        $cost_per_sqft = 1500;
        break;
    case 'villa':
        $cost_per_sqft = 2000;
        break;
    case 'apartment':
        $cost_per_sqft = 1800;
        break;
    case 'duplex':
        $cost_per_sqft = 1700;
        break;
    case 'commercial':
        $cost_per_sqft = 2200;
        break;
    default:
        $cost_per_sqft = 1500;
}

// Convert plot area from sq yards to sq ft (1 sq yard = 9 sq ft)
$plot_area_sqft = $plot_area * 9;

// Calculate total construction cost
$total_cost = $plot_area_sqft * $cost_per_sqft * $floors;

// Calculate construction duration (in months)
$duration = ceil(($plot_area_sqft * $floors) / 1000);
if ($duration < 6) $duration = 6; // Minimum 6 months

// Define building plans
$plans = [
    [
        'id' => 1,
        'name' => 'Modern Minimalist',
        'description' => 'A sleek and modern design with open floor plans and clean lines.',
        'cost' => $total_cost * 1.0,
        'duration' => $duration,
        'image' => 'images/plans/modern_minimalist.jpg',
        'materials' => [
            'Cement' => ceil($plot_area_sqft * 0.045) . ' bags',
            'Steel' => ceil($plot_area_sqft * 0.007) . ' tonnes',
            'Sand' => ceil($plot_area_sqft * 0.016) . ' cubic meters',
            'Bricks' => ceil($plot_area_sqft * 8) . ' pieces',
            'Paint' => ceil($plot_area_sqft * 0.1) . ' liters'
        ],
        'workers' => [
            'Mason' => ceil($plot_area_sqft / 500),
            'Carpenter' => ceil($plot_area_sqft / 700),
            'Electrician' => ceil($plot_area_sqft / 1000),
            'Plumber' => ceil($plot_area_sqft / 1000),
            'Painter' => ceil($plot_area_sqft / 800),
            'Laborers' => ceil($plot_area_sqft / 300)
        ]
    ],
    [
        'id' => 2,
        'name' => 'Traditional Elegance',
        'description' => 'A classic design with traditional elements and spacious rooms.',
        'cost' => $total_cost * 1.1,
        'duration' => $duration + 1,
        'image' => 'images/plans/traditional.jpg',
        'materials' => [
            'Cement' => ceil($plot_area_sqft * 0.05) . ' bags',
            'Steel' => ceil($plot_area_sqft * 0.008) . ' tonnes',
            'Sand' => ceil($plot_area_sqft * 0.018) . ' cubic meters',
            'Bricks' => ceil($plot_area_sqft * 9) . ' pieces',
            'Paint' => ceil($plot_area_sqft * 0.12) . ' liters'
        ],
        'workers' => [
            'Mason' => ceil($plot_area_sqft / 450),
            'Carpenter' => ceil($plot_area_sqft / 600),
            'Electrician' => ceil($plot_area_sqft / 900),
            'Plumber' => ceil($plot_area_sqft / 900),
            'Painter' => ceil($plot_area_sqft / 700),
            'Laborers' => ceil($plot_area_sqft / 250)
        ]
    ],
    [
        'id' => 3,
        'name' => 'Eco-Friendly',
        'description' => 'A sustainable design with energy-efficient features and natural materials.',
        'cost' => $total_cost * 1.15,
        'duration' => $duration + 1,
        'image' => 'images/plans/eco_friendly.jpg',
        'materials' => [
            'Cement' => ceil($plot_area_sqft * 0.04) . ' bags',
            'Steel' => ceil($plot_area_sqft * 0.006) . ' tonnes',
            'Sand' => ceil($plot_area_sqft * 0.015) . ' cubic meters',
            'Bricks' => ceil($plot_area_sqft * 7) . ' pieces',
            'Paint' => ceil($plot_area_sqft * 0.08) . ' liters (eco-friendly)'
        ],
        'workers' => [
            'Mason' => ceil($plot_area_sqft / 550),
            'Carpenter' => ceil($plot_area_sqft / 750),
            'Electrician' => ceil($plot_area_sqft / 950),
            'Plumber' => ceil($plot_area_sqft / 950),
            'Painter' => ceil($plot_area_sqft / 850),
            'Laborers' => ceil($plot_area_sqft / 320)
        ]
    ],
    [
        'id' => 4,
        'name' => 'Luxury Living',
        'description' => 'An upscale design with premium finishes and high-end amenities.',
        'cost' => $total_cost * 1.3,
        'duration' => $duration + 2,
        'image' => 'images/plans/luxury.jpg',
        'materials' => [
            'Cement' => ceil($plot_area_sqft * 0.055) . ' bags',
            'Steel' => ceil($plot_area_sqft * 0.009) . ' tonnes',
            'Sand' => ceil($plot_area_sqft * 0.02) . ' cubic meters',
            'Bricks' => ceil($plot_area_sqft * 10) . ' pieces',
            'Paint' => ceil($plot_area_sqft * 0.14) . ' liters (premium)'
        ],
        'workers' => [
            'Mason' => ceil($plot_area_sqft / 400),
            'Carpenter' => ceil($plot_area_sqft / 550),
            'Electrician' => ceil($plot_area_sqft / 800),
            'Plumber' => ceil($plot_area_sqft / 800),
            'Painter' => ceil($plot_area_sqft / 600),
            'Laborers' => ceil($plot_area_sqft / 200)
        ]
    ],
    [
        'id' => 5,
        'name' => 'Budget Friendly',
        'description' => 'A cost-effective design without compromising on functionality.',
        'cost' => $total_cost * 0.85,
        'duration' => $duration,
        'image' => 'images/plans/budget.jpg',
        'materials' => [
            'Cement' => ceil($plot_area_sqft * 0.04) . ' bags',
            'Steel' => ceil($plot_area_sqft * 0.006) . ' tonnes',
            'Sand' => ceil($plot_area_sqft * 0.014) . ' cubic meters',
            'Bricks' => ceil($plot_area_sqft * 7) . ' pieces',
            'Paint' => ceil($plot_area_sqft * 0.08) . ' liters'
        ],
        'workers' => [
            'Mason' => ceil($plot_area_sqft / 600),
            'Carpenter' => ceil($plot_area_sqft / 800),
            'Electrician' => ceil($plot_area_sqft / 1200),
            'Plumber' => ceil($plot_area_sqft / 1200),
            'Painter' => ceil($plot_area_sqft / 1000),
            'Laborers' => ceil($plot_area_sqft / 350)
        ]
    ]
];

// Filter plans based on budget
$affordable_plans = array_filter($plans, function($plan) use ($budget) {
    return $plan['cost'] <= $budget;
});

// If no plans are affordable, include the most affordable one
if (empty($affordable_plans) && !empty($plans)) {
    usort($plans, function($a, $b) {
        return $a['cost'] <=> $b['cost'];
    });
    $affordable_plans = [$plans[0]];
}

// Get selected plan details
$selected_plan = null;
if (isset($_GET['plan_id']) && is_numeric($_GET['plan_id'])) {
    $plan_id = $_GET['plan_id'];
    foreach ($plans as $plan) {
        if ($plan['id'] == $plan_id) {
            $selected_plan = $plan;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Plans - Home Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Building Plans</h1>
            <p>Explore design options based on your requirements</p>
            <div class="budget-pill">
                <span>Available Budget: </span>
                <strong>₹<?php echo number_format($remaining_budget); ?></strong>
            </div>
        </div>
        
        <?php if (empty($affordable_plans)): ?>
            <div class="alert alert-error">
                <p>No building plans match your current budget. Please consider increasing your budget or exploring custom options.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($selected_plan): ?>
            <div class="plan-details-container">
                <div class="plan-details-header">
                    <h2><?php echo $selected_plan['name']; ?> Plan</h2>
                    <a href="building_plans.php" class="btn btn-secondary">Back to Plans</a>
                </div>
                
                <div class="plan-details-grid">
                    <div class="plan-image-container">
                        <img src="<?php echo $selected_plan['image']; ?>" alt="<?php echo $selected_plan['name']; ?> Plan" class="plan-image">
                    </div>
                    
                    <div class="plan-info">
                        <p class="plan-description"><?php echo $selected_plan['description']; ?></p>
                        
                        <div class="plan-stats">
                            <div class="stat-item">
                                <i class="fas fa-coins"></i>
                                <div>
                                    <h4>Estimated Cost</h4>
                                    <p>₹<?php echo number_format($selected_plan['cost']); ?></p>
                                    <?php if ($selected_plan['cost'] > $remaining_budget): ?>
                                        <span class="budget-warning">Exceeds your budget by ₹<?php echo number_format($selected_plan['cost'] - $remaining_budget); ?></span>
                                    <?php else: ?>
                                        <span class="budget-ok">Within your budget</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <h4>Estimated Duration</h4>
                                    <p><?php echo $selected_plan['duration']; ?> months</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="materials-list">
                            <h3>Materials Required</h3>
                            <ul>
                                <?php foreach ($selected_plan['materials'] as $material => $quantity): ?>
                                    <li>
                                        <span class="material-name"><?php echo $material; ?></span>
                                        <span class="material-quantity"><?php echo $quantity; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="workers-list">
                            <h3>Workers Required</h3>
                            <ul>
                                <?php foreach ($selected_plan['workers'] as $worker => $count): ?>
                                    <li>
                                        <span class="worker-type"><?php echo $worker; ?></span>
                                        <span class="worker-count"><?php echo $count; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="plan-actions">
                            <a href="workers.php" class="btn btn-primary">Find Workers</a>
                            <a href="marketplace.php" class="btn btn-secondary">Shop Materials</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="plans-grid">
                <?php foreach ($plans as $plan): ?>
                    <div class="plan-card <?php echo ($plan['cost'] > $remaining_budget) ? 'over-budget' : ''; ?>">
                        <div class="plan-image-container">
                            <img src="<?php echo $plan['image']; ?>" alt="<?php echo $plan['name']; ?> Plan" class="plan-thumbnail">
                            <?php if ($plan['cost'] > $remaining_budget): ?>
                                <div class="budget-badge warning">Over Budget</div>
                            <?php else: ?>
                                <div class="budget-badge success">Within Budget</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="plan-card-content">
                            <h3><?php echo $plan['name']; ?></h3>
                            <p class="plan-card-description"><?php echo $plan['description']; ?></p>
                            
                            <div class="plan-card-stats">
                                <div class="stat">
                                    <i class="fas fa-coins"></i>
                                    <span>₹<?php echo number_format($plan['cost']); ?></span>
                                </div>
                                
                                <div class="stat">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo $plan['duration']; ?> months</span>
                                </div>
                            </div>
                            
                            <a href="building_plans.php?plan_id=<?php echo $plan['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <script src="js/script.js"></script>
</body>
</html>