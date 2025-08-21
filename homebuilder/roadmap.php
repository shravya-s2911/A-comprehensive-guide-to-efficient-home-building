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

// Define construction roadmap steps
$roadmap_steps = [
    [
        'id' => 1,
        'name' => 'Planning & Design',
        'description' => 'Create detailed architectural and structural plans for your building.',
        'tasks' => [
            'Hire an architect',
            'Develop floor plans',
            'Create elevation designs',
            'Prepare structural drawings',
            'Finalize design and make revisions'
        ],
        'duration' => '4-6 weeks',
        'est_cost' => '3-5% of total budget',
        'next_step' => 'Apply for necessary permits and approvals from local authorities.'
    ],
    [
        'id' => 2,
        'name' => 'Permits & Approvals',
        'description' => 'Obtain all necessary legal permissions and clearances to begin construction.',
        'tasks' => [
            'Apply for building permit',
            'Get plan sanction from GHMC/local authority',
            'Obtain water and sewage connection approvals',
            'Get electricity connection approval',
            'Environmental clearance (if required)'
        ],
        'duration' => '2-4 weeks',
        'est_cost' => '1-2% of total budget',
        'next_step' => 'Hire a contractor and finalize construction team for your project.'
    ],
    [
        'id' => 3,
        'name' => 'Hiring & Team Setup',
        'description' => 'Select qualified contractors and workers for your construction project.',
        'tasks' => [
            'Gather quotes from multiple contractors',
            'Check references and past work',
            'Finalize contract with selected team',
            'Set up payment schedule',
            'Create construction timeline'
        ],
        'duration' => '2-3 weeks',
        'est_cost' => 'No direct cost (management time)',
        'next_step' => 'Procure essential construction materials for foundation and structure.'
    ],
    [
        'id' => 4,
        'name' => 'Material Procurement',
        'description' => 'Purchase and arrange delivery of construction materials.',
        'tasks' => [
            'Source quotes for major materials',
            'Purchase cement, sand, and aggregates',
            'Order steel reinforcement',
            'Arrange for bricks/blocks',
            'Schedule deliveries according to construction phases'
        ],
        'duration' => 'Ongoing throughout construction',
        'est_cost' => '50-60% of total budget',
        'next_step' => 'Begin the foundation work and site preparation.'
    ],
    [
        'id' => 5,
        'name' => 'Foundation & Structure',
        'description' => 'Build the foundation and main structural elements of the building.',
        'tasks' => [
            'Site clearance and excavation',
            'Lay foundation',
            'Construct columns and beams',
            'Build walls for ground floor',
            'Complete roof/slab work for ground floor',
            'Repeat for additional floors'
        ],
        'duration' => '2-4 months',
        'est_cost' => '30-35% of total budget',
        'next_step' => 'Install electrical wiring, plumbing, and other utilities.'
    ],
    [
        'id' => 6,
        'name' => 'Utilities & Services',
        'description' => 'Install all necessary electrical, plumbing, and utility systems.',
        'tasks' => [
            'Electrical wiring throughout the building',
            'Plumbing installation',
            'HVAC system setup (if applicable)',
            'Internet and cable wiring',
            'Security system installation'
        ],
        'duration' => '3-5 weeks',
        'est_cost' => '8-10% of total budget',
        'next_step' => 'Complete interior finishes like flooring, painting, and fixtures.'
    ],
    [
        'id' => 7,
        'name' => 'Interior Finishes',
        'description' => 'Complete all interior work to make the space livable.',
        'tasks' => [
            'Plastering of walls',
            'Flooring installation',
            'Painting and wall finishes',
            'Kitchen and bathroom fixtures',
            'Doors and windows installation',
            'Cabinetry and built-in furniture'
        ],
        'duration' => '6-8 weeks',
        'est_cost' => '15-20% of total budget',
        'next_step' => 'Finalize exterior finishes and landscaping.'
    ],
    [
        'id' => 8,
        'name' => 'Exterior & Landscaping',
        'description' => 'Complete the exterior appearance and surrounding areas of the building.',
        'tasks' => [
            'Exterior painting/cladding',
            'Driveway and pathways',
            'Garden and landscaping',
            'Fencing or boundary walls',
            'Exterior lighting'
        ],
        'duration' => '2-4 weeks',
        'est_cost' => '5-8% of total budget',
        'next_step' => 'Conduct final inspections and obtain completion certificates.'
    ],
    [
        'id' => 9,
        'name' => 'Final Inspections',
        'description' => 'Ensure all work meets quality standards and obtain necessary completion certificates.',
        'tasks' => [
            'Building inspection by authorities',
            'Electrical system inspection',
            'Plumbing system testing',
            'Obtain occupancy certificate',
            'Final contractor walkthrough'
        ],
        'duration' => '1-2 weeks',
        'est_cost' => '1% of total budget',
        'next_step' => 'Move in and enjoy your new building!'
    ]
];

// Get selected step details
$selected_step = null;
if (isset($_GET['step_id']) && is_numeric($_GET['step_id'])) {
    $step_id = $_GET['step_id'];
    foreach ($roadmap_steps as $step) {
        if ($step['id'] == $step_id) {
            $selected_step = $step;
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
    <title>Construction Roadmap - Home Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Construction Roadmap</h1>
            <p>Follow this step-by-step guide to complete your construction project</p>
            <div class="budget-pill">
                <span>Available Budget: </span>
                <strong>₹<?php echo number_format($remaining_budget); ?></strong>
            </div>
        </div>
        
        <?php if ($selected_step): ?>
            <div class="step-details-container">
                <div class="step-details-header">
                    <h2>Step <?php echo $selected_step['id']; ?>: <?php echo $selected_step['name']; ?></h2>
                    <a href="roadmap.php" class="btn btn-secondary">Back to Roadmap</a>
                </div>
                
                <div class="step-details-grid">
                    <div class="step-info">
                        <div class="step-details-card">
                            <p class="step-description"><?php echo $selected_step['description']; ?></p>
                            
                            <div class="step-stats">
                                <div class="stat">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <h4>Estimated Duration</h4>
                                        <p><?php echo $selected_step['duration']; ?></p>
                                    </div>
                                </div>
                                
                                <div class="stat">
                                    <i class="fas fa-coins"></i>
                                    <div>
                                        <h4>Estimated Cost</h4>
                                        <p><?php echo $selected_step['est_cost']; ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tasks-list">
                                <h3>Key Tasks</h3>
                                <ul>
                                    <?php foreach ($selected_step['tasks'] as $task): ?>
                                        <li>
                                            <i class="fas fa-check-circle"></i>
                                            <span><?php echo $task; ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="next-step">
                                <h3>Next Step</h3>
                                <p><?php echo $selected_step['next_step']; ?></p>
                            </div>
                            
                            <div class="step-actions">
                                <?php if ($selected_step['id'] > 1): ?>
                                    <a href="roadmap.php?step_id=<?php echo $selected_step['id'] - 1; ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Previous Step
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($selected_step['id'] < count($roadmap_steps)): ?>
                                    <a href="roadmap.php?step_id=<?php echo $selected_step['id'] + 1; ?>" class="btn btn-primary">
                                        Next Step <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="related-links">
                                <h3>Helpful Resources</h3>
                                <div class="related-links-grid">
                                    <?php if ($selected_step['id'] == 1): ?>
                                        <a href="workers.php?worker_type=architect" class="related-link">
                                            <i class="fas fa-hard-hat"></i>
                                            <span>Find Architects</span>
                                        </a>
                                    <?php elseif ($selected_step['id'] == 2): ?>
                                        <a href="permits.php" class="related-link">
                                            <i class="fas fa-file-contract"></i>
                                            <span>Legal Permits Guide</span>
                                        </a>
                                    <?php elseif ($selected_step['id'] == 3): ?>
                                        <a href="workers.php?worker_type=contractor" class="related-link">
                                            <i class="fas fa-hard-hat"></i>
                                            <span>Find Contractors</span>
                                        </a>
                                    <?php elseif ($selected_step['id'] == 4): ?>
                                        <a href="marketplace.php" class="related-link">
                                            <i class="fas fa-shopping-cart"></i>
                                            <span>Materials Marketplace</span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="progress_log.php" class="related-link">
                                        <i class="fas fa-tasks"></i>
                                        <span>Track Expenses</span>
                                    </a>
                                    
                                    <a href="forum.php" class="related-link">
                                        <i class="fas fa-comments"></i>
                                        <span>Ask Community</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="roadmap-container">
                <div class="roadmap-timeline">
                    <?php foreach ($roadmap_steps as $step): ?>
                        <div class="roadmap-step">
                            <div class="step-icon">
                                <div class="step-number"><?php echo $step['id']; ?></div>
                            </div>
                            
                            <div class="step-content">
                                <h3><?php echo $step['name']; ?></h3>
                                <p><?php echo $step['description']; ?></p>
                                <div class="step-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $step['duration']; ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-coins"></i>
                                        <span><?php echo $step['est_cost']; ?></span>
                                    </div>
                                </div>
                                <a href="roadmap.php?step_id=<?php echo $step['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <script src="js/script.js"></script>
</body>
</html>