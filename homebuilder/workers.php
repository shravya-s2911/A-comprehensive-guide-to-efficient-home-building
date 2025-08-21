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

// Get location from building details
$address = $building_details['plot_address'];
$location_parts = explode(',', $address);
$city = trim(end($location_parts));

// Get filter parameters
$worker_type = isset($_GET['worker_type']) ? $_GET['worker_type'] : 'all';
$min_rating = isset($_GET['min_rating']) ? (float)$_GET['min_rating'] : 0;

// Define worker types
$worker_types = [
    'all' => 'All Workers',
    'mason' => 'Masons',
    'carpenter' => 'Carpenters',
    'electrician' => 'Electricians',
    'plumber' => 'Plumbers',
    'painter' => 'Painters',
    'labor' => 'General Labor',
    'contractor' => 'Contractors',
    'architect' => 'Architects',
    'engineer' => 'Civil Engineers'
];

// Mock workers database (in a real app, this would come from a database)
$workers = [
    [
        'id' => 1,
        'name' => 'Rama Construction Team',
        'type' => 'contractor',
        'experience' => 15,
        'workers_count' => 25,
        'rate_type' => 'project',
        'rate' => 'Varies by project size',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543230',
        'description' => 'Full-service construction contractor with expertise in residential buildings. We handle everything from foundation to finishing.',
        'rating' => 4.8,
        'projects_completed' => 75
    ],
    [
        'id' => 2,
        'name' => 'Krishna Master Mason Group',
        'type' => 'mason',
        'experience' => 12,
        'workers_count' => 8,
        'rate_type' => 'daily',
        'rate' => '₹800-1000 per person per day',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543231',
        'description' => 'Specialized in brickwork, plastering, and flooring. Our team has worked on over 100 residential projects.',
        'rating' => 4.6,
        'projects_completed' => 120
    ],
    [
        'id' => 3,
        'name' => 'Lakshmi Carpentry Works',
        'type' => 'carpenter',
        'experience' => 10,
        'workers_count' => 6,
        'rate_type' => 'daily',
        'rate' => '₹900-1200 per person per day',
        'location' => 'Secunderabad',
        'contact' => '+91 9876543232',
        'description' => 'Custom woodworking, door and window installation, and furniture making. Quality craftsmanship guaranteed.',
        'rating' => 4.7,
        'projects_completed' => 90
    ],
    [
        'id' => 4,
        'name' => 'Raju Electrical Solutions',
        'type' => 'electrician',
        'experience' => 8,
        'workers_count' => 5,
        'rate_type' => 'project',
        'rate' => 'From ₹15,000 for full house wiring',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543233',
        'description' => 'Complete electrical solutions including wiring, fixtures installation, and troubleshooting. Licensed and certified.',
        'rating' => 4.5,
        'projects_completed' => 65
    ],
    [
        'id' => 5,
        'name' => 'Venkat Plumbing Services',
        'type' => 'plumber',
        'experience' => 9,
        'workers_count' => 4,
        'rate_type' => 'project',
        'rate' => 'From ₹12,000 for full house plumbing',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543234',
        'description' => 'Expert plumbing installation and repair. Specializing in bathroom and kitchen fixtures, water supply, and drainage systems.',
        'rating' => 4.4,
        'projects_completed' => 70
    ],
    [
        'id' => 6,
        'name' => 'Colorworld Painting Services',
        'type' => 'painter',
        'experience' => 7,
        'workers_count' => 10,
        'rate_type' => 'sqft',
        'rate' => '₹18-25 per sq ft',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543235',
        'description' => 'Interior and exterior painting with premium quality paints. Texture finishes, wall designs, and waterproofing solutions available.',
        'rating' => 4.6,
        'projects_completed' => 85
    ],
    [
        'id' => 7,
        'name' => 'Nagaraju Labor Supply',
        'type' => 'labor',
        'experience' => 5,
        'workers_count' => 30,
        'rate_type' => 'daily',
        'rate' => '₹500-600 per person per day',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543236',
        'description' => 'Reliable general labor for construction sites. Available for short-term and long-term projects.',
        'rating' => 4.2,
        'projects_completed' => 100
    ],
    [
        'id' => 8,
        'name' => 'Modern Home Architects',
        'type' => 'architect',
        'experience' => 12,
        'workers_count' => 3,
        'rate_type' => 'project',
        'rate' => 'From ₹30,000 for house plans',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543237',
        'description' => 'Creative architectural design for residential and commercial buildings. 3D visualization and detailed blueprints provided.',
        'rating' => 4.9,
        'projects_completed' => 45
    ],
    [
        'id' => 9,
        'name' => 'Structural Solutions Engineering',
        'type' => 'engineer',
        'experience' => 15,
        'workers_count' => 4,
        'rate_type' => 'project',
        'rate' => 'From ₹20,000 for structural design',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543238',
        'description' => 'Structural engineering consultancy for residential and commercial projects. Soil testing, foundation design, and structural analysis.',
        'rating' => 4.8,
        'projects_completed' => 60
    ],
    [
        'id' => 10,
        'name' => 'Rao Construction Company',
        'type' => 'contractor',
        'experience' => 20,
        'workers_count' => 50,
        'rate_type' => 'project',
        'rate' => 'Varies by project size',
        'location' => 'Secunderabad',
        'contact' => '+91 9876543239',
        'description' => 'Full-service construction company with expertise in residential, commercial, and industrial projects. Quality and timely completion guaranteed.',
        'rating' => 4.7,
        'projects_completed' => 120
    ]
];

// Filter workers based on criteria
$filtered_workers = $workers;

if ($worker_type !== 'all') {
    $filtered_workers = array_filter($filtered_workers, function($worker) use ($worker_type) {
        return $worker['type'] === $worker_type;
    });
}

if ($min_rating > 0) {
    $filtered_workers = array_filter($filtered_workers, function($worker) use ($min_rating) {
        return $worker['rating'] >= $min_rating;
    });
}

// Get single worker details
$single_worker = null;
if (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) {
    $worker_id = $_GET['worker_id'];
    foreach ($workers as $worker) {
        if ($worker['id'] == $worker_id) {
            $single_worker = $worker;
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
    <title>Workers - Home Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Workers & Contractors</h1>
            <p>Find skilled workers and contractors for your construction project</p>
            <div class="budget-pill">
                <span>Available Budget: </span>
                <strong>₹<?php echo number_format($remaining_budget); ?></strong>
            </div>
        </div>
        
        <?php if ($single_worker): ?>
            <div class="worker-details-container">
                <div class="worker-details-header">
                    <h2><?php echo $single_worker['name']; ?></h2>
                    <a href="workers.php" class="btn btn-secondary">Back to Workers</a>
                </div>
                
                <div class="worker-details-grid">
                    <div class="worker-info">
                        <div class="worker-details-card">
                            <div class="worker-type-badge"><?php echo $worker_types[$single_worker['type']]; ?></div>
                            
                            <div class="worker-stats">
                                <div class="stat">
                                    <i class="fas fa-star"></i>
                                    <div>
                                        <h4>Rating</h4>
                                        <p><?php echo $single_worker['rating']; ?>/5</p>
                                    </div>
                                </div>
                                
                                <div class="stat">
                                    <i class="fas fa-briefcase"></i>
                                    <div>
                                        <h4>Experience</h4>
                                        <p><?php echo $single_worker['experience']; ?> years</p>
                                    </div>
                                </div>
                                
                                <div class="stat">
                                    <i class="fas fa-clipboard-check"></i>
                                    <div>
                                        <h4>Projects</h4>
                                        <p><?php echo $single_worker['projects_completed']; ?> completed</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="worker-description">
                                <h3>About</h3>
                                <p><?php echo $single_worker['description']; ?></p>
                            </div>
                            
                            <div class="worker-details-info">
                                <div class="info-item">
                                    <h4>Team Size</h4>
                                    <p><?php echo $single_worker['workers_count']; ?> workers</p>
                                </div>
                                
                                <div class="info-item">
                                    <h4>Rate</h4>
                                    <p><?php echo $single_worker['rate']; ?></p>
                                    <p class="rate-type">(<?php echo ucfirst($single_worker['rate_type']); ?> based)</p>
                                </div>
                                
                                <div class="info-item">
                                    <h4>Location</h4>
                                    <p><?php echo $single_worker['location']; ?></p>
                                </div>
                                
                                <div class="info-item">
                                    <h4>Contact</h4>
                                    <p><?php echo $single_worker['contact']; ?></p>
                                </div>
                            </div>
                            
                            <div class="worker-actions">
                                <a href="tel:<?php echo str_replace(' ', '', $single_worker['contact']); ?>" class="btn btn-primary">Contact Now</a>
                                <a href="progress_log.php" class="btn btn-secondary">Track in Budget</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="similar-workers">
                        <h3>Similar Workers</h3>
                        <div class="similar-workers-grid">
                            <?php
                                $related = array_filter($workers, function($worker) use ($single_worker) {
                                    return $worker['type'] == $single_worker['type'] && $worker['id'] != $single_worker['id'];
                                });
                                
                                $related = array_slice($related, 0, 2);
                                
                                if (empty($related)) {
                                    $related = array_filter($workers, function($worker) use ($single_worker) {
                                        return $worker['id'] != $single_worker['id'];
                                    });
                                    $related = array_slice($related, 0, 2);
                                }
                                
                                foreach ($related as $worker):
                            ?>
                                <div class="worker-card small">
                                    <div class="worker-type-badge small"><?php echo $worker_types[$worker['type']]; ?></div>
                                    <div class="worker-card-content">
                                        <h4><?php echo $worker['name']; ?></h4>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo $worker['rating']; ?></span>
                                        </div>
                                        <a href="workers.php?worker_id=<?php echo $worker['id']; ?>" class="btn btn-small">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="workers-container">
                <div class="workers-sidebar">
                    <div class="filter-card">
                        <h3>Filter Workers</h3>
                        <form method="GET" action="" class="workers-filter-form">
                            <div class="form-group">
                                <label for="worker_type">Worker Type</label>
                                <select id="worker_type" name="worker_type">
                                    <?php foreach ($worker_types as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($worker_type == $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="min_rating">Minimum Rating</label>
                                <div class="rating-selector">
                                    <input type="range" id="min_rating" name="min_rating" min="0" max="5" step="0.5" value="<?php echo $min_rating; ?>">
                                    <div class="rating-display">
                                        <i class="fas fa-star"></i>
                                        <span id="ratingValue"><?php echo $min_rating; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="workers.php" class="btn btn-secondary">Reset</a>
                        </form>
                    </div>
                    
                    <div class="info-card">
                        <h3>Hiring Tips</h3>
                        <ul class="tips-list">
                            <li>Check references and past work before hiring</li>
                            <li>Get detailed quotes in writing</li>
                            <li>Discuss timelines and expectations clearly</li>
                            <li>Verify proper licenses and insurance</li>
                            <li>Never pay the full amount upfront</li>
                        </ul>
                    </div>
                </div>
                
                <div class="workers-content">
                    <?php if (empty($filtered_workers)): ?>
                        <div class="no-results">
                            <i class="fas fa-hard-hat"></i>
                            <p>No workers found matching your criteria.</p>
                            <p>Try adjusting your filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="workers-grid">
                            <?php foreach ($filtered_workers as $worker): ?>
                                <div class="worker-card">
                                    <div class="worker-type-badge"><?php echo $worker_types[$worker['type']]; ?></div>
                                    <div class="worker-card-content">
                                        <h3><?php echo $worker['name']; ?></h3>
                                        <p class="worker-brief"><?php echo substr($worker['description'], 0, 100); ?>...</p>
                                        
                                        <div class="worker-info-grid">
                                            <div class="worker-info-item">
                                                <i class="fas fa-star"></i>
                                                <span><?php echo $worker['rating']; ?></span>
                                            </div>
                                            
                                            <div class="worker-info-item">
                                                <i class="fas fa-briefcase"></i>
                                                <span><?php echo $worker['experience']; ?> years</span>
                                            </div>
                                            
                                            <div class="worker-info-item">
                                                <i class="fas fa-users"></i>
                                                <span><?php echo $worker['workers_count']; ?> workers</span>
                                            </div>
                                            
                                            <div class="worker-info-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo $worker['location']; ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="worker-rate">
                                            <span><?php echo $worker['rate']; ?></span>
                                        </div>
                                        
                                        <a href="workers.php?worker_id=<?php echo $worker['id']; ?>" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <script>
        // Update rating display
        const ratingRange = document.getElementById('min_rating');
        const ratingValue = document.getElementById('ratingValue');
        
        if (ratingRange && ratingValue) {
            ratingRange.addEventListener('input', function() {
                ratingValue.textContent = this.value;
            });
        }
    </script>
    
    <script src="js/script.js"></script>
</body>
</html>