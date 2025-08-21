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

// Get search parameters
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : $remaining_budget;

// Define material categories
$categories = [
    'all' => 'All Categories',
    'cement' => 'Cement',
    'steel' => 'Steel',
    'bricks' => 'Bricks',
    'sand' => 'Sand',
    'aggregates' => 'Aggregates',
    'tiles' => 'Tiles',
    'paint' => 'Paint',
    'electrical' => 'Electrical',
    'plumbing' => 'Plumbing',
    'wood' => 'Wood',
    'roofing' => 'Roofing',
    'hardware' => 'Hardware'
];

// Mock materials database (in a real app, this would come from a database)
$materials = [
    [
        'id' => 1,
        'name' => 'Ultra Tech Cement',
        'category' => 'cement',
        'description' => 'High quality cement for all construction needs',
        'unit' => 'Bag (50 kg)',
        'price' => 350,
        'supplier' => 'GS Construction Supplies',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543210',
        'rating' => 4.5
    ],
    [
        'id' => 2,
        'name' => 'JSW Steel TMT Bars',
        'category' => 'steel',
        'description' => 'Fe 500 TMT steel bars for reinforcement',
        'unit' => 'Ton',
        'price' => 65000,
        'supplier' => 'Sri Rama Steel',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543211',
        'rating' => 4.7
    ],
    [
        'id' => 3,
        'name' => 'Red Clay Bricks',
        'category' => 'bricks',
        'description' => 'Standard size red clay bricks',
        'unit' => '1000 pcs',
        'price' => 6000,
        'supplier' => 'Lakshmi Brick Works',
        'location' => 'Secunderabad',
        'contact' => '+91 9876543212',
        'rating' => 4.3
    ],
    [
        'id' => 4,
        'name' => 'River Sand',
        'category' => 'sand',
        'description' => 'Clean river sand for construction',
        'unit' => 'Cubic Meter',
        'price' => 2800,
        'supplier' => 'Krishna Sand Suppliers',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543213',
        'rating' => 4.0
    ],
    [
        'id' => 5,
        'name' => 'Asian Paints Premium Emulsion',
        'category' => 'paint',
        'description' => 'Premium quality emulsion paint for interior walls',
        'unit' => '20 Liter',
        'price' => 3500,
        'supplier' => 'Color World',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543214',
        'rating' => 4.8
    ],
    [
        'id' => 6,
        'name' => 'Kajaria Ceramic Floor Tiles',
        'category' => 'tiles',
        'description' => 'Ceramic floor tiles, various designs',
        'unit' => 'Box (8 tiles)',
        'price' => 1200,
        'supplier' => 'Tile House',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543215',
        'rating' => 4.6
    ],
    [
        'id' => 7,
        'name' => 'Havells Electrical Wiring',
        'category' => 'electrical',
        'description' => '2.5 sq mm copper electrical wires',
        'unit' => '100 meters',
        'price' => 2500,
        'supplier' => 'Electrical Depot',
        'location' => 'Secunderabad',
        'contact' => '+91 9876543216',
        'rating' => 4.5
    ],
    [
        'id' => 8,
        'name' => 'Astral PVC Pipes',
        'category' => 'plumbing',
        'description' => 'PVC pipes for plumbing, 1 inch diameter',
        'unit' => '10 feet',
        'price' => 180,
        'supplier' => 'Plumbing Solutions',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543217',
        'rating' => 4.4
    ],
    [
        'id' => 9,
        'name' => 'Coarse Aggregates',
        'category' => 'aggregates',
        'description' => '20mm size crushed stone aggregates',
        'unit' => 'Cubic Meter',
        'price' => 1800,
        'supplier' => 'Rock Crushers',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543218',
        'rating' => 4.2
    ],
    [
        'id' => 10,
        'name' => 'Plyboard Sheets',
        'category' => 'wood',
        'description' => 'Commercial grade plywood sheets',
        'unit' => 'Sheet (8x4 feet)',
        'price' => 1500,
        'supplier' => 'Wood World',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543219',
        'rating' => 4.3
    ],
    [
        'id' => 11,
        'name' => 'Tata Roofing Sheets',
        'category' => 'roofing',
        'description' => 'Galvanized corrugated roofing sheets',
        'unit' => 'Sheet (10 feet)',
        'price' => 650,
        'supplier' => 'Roof Solutions',
        'location' => 'Secunderabad',
        'contact' => '+91 9876543220',
        'rating' => 4.5
    ],
    [
        'id' => 12,
        'name' => 'Door Hardware Set',
        'category' => 'hardware',
        'description' => 'Complete door hardware set including handles, locks, and hinges',
        'unit' => 'Set',
        'price' => 1200,
        'supplier' => 'Hardware House',
        'location' => 'Hyderabad',
        'contact' => '+91 9876543221',
        'rating' => 4.4
    ]
];

// Filter materials based on search criteria
$filtered_materials = $materials;

if (!empty($search_term)) {
    $filtered_materials = array_filter($filtered_materials, function($material) use ($search_term) {
        return (
            stripos($material['name'], $search_term) !== false ||
            stripos($material['description'], $search_term) !== false ||
            stripos($material['supplier'], $search_term) !== false
        );
    });
}

if ($category !== 'all') {
    $filtered_materials = array_filter($filtered_materials, function($material) use ($category) {
        return $material['category'] === $category;
    });
}

if ($max_price > 0) {
    $filtered_materials = array_filter($filtered_materials, function($material) use ($max_price) {
        return $material['price'] <= $max_price;
    });
}

// Get single material details
$single_material = null;
if (isset($_GET['material_id']) && is_numeric($_GET['material_id'])) {
    $material_id = $_GET['material_id'];
    foreach ($materials as $material) {
        if ($material['id'] == $material_id) {
            $single_material = $material;
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
    <title>Marketplace - Home Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Marketplace</h1>
            <p>Find construction materials and supplies for your project</p>
            <div class="budget-pill">
                <span>Available Budget: </span>
                <strong>₹<?php echo number_format($remaining_budget); ?></strong>
            </div>
        </div>
        
        <?php if ($single_material): ?>
            <div class="material-details-container">
                <div class="material-details-header">
                    <h2><?php echo $single_material['name']; ?></h2>
                    <a href="marketplace.php" class="btn btn-secondary">Back to Marketplace</a>
                </div>
                
                <div class="material-details-grid">
                    <div class="material-info">
                        <div class="material-details-card">
                            <div class="material-category"><?php echo $categories[$single_material['category']]; ?></div>
                            <p class="material-description"><?php echo $single_material['description']; ?></p>
                            
                            <div class="price-info">
                                <h3>₹<?php echo number_format($single_material['price']); ?></h3>
                                <span>per <?php echo $single_material['unit']; ?></span>
                            </div>
                            
                            <div class="supplier-info">
                                <h3>Supplier Information</h3>
                                <p><strong>Name:</strong> <?php echo $single_material['supplier']; ?></p>
                                <p><strong>Location:</strong> <?php echo $single_material['location']; ?></p>
                                <p><strong>Contact:</strong> <?php echo $single_material['contact']; ?></p>
                                <p><strong>Rating:</strong> 
                                    <span class="star-rating">
                                        <?php
                                            $rating = $single_material['rating'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } elseif ($i - 0.5 <= $rating) {
                                                    echo '<i class="fas fa-star-half-alt"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                        ?>
                                        <span>(<?php echo $rating; ?>)</span>
                                    </span>
                                </p>
                            </div>
                            
                            <div class="material-actions">
                                <a href="tel:<?php echo str_replace(' ', '', $single_material['contact']); ?>" class="btn btn-primary">Contact Supplier</a>
                                <a href="progress_log.php" class="btn btn-secondary">Track in Budget</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="related-materials">
                        <h3>Similar Materials</h3>
                        <div class="related-materials-grid">
                            <?php
                                $related = array_filter($materials, function($material) use ($single_material) {
                                    return $material['category'] == $single_material['category'] && $material['id'] != $single_material['id'];
                                });
                                
                                $related = array_slice($related, 0, 3);
                                
                                foreach ($related as $material):
                            ?>
                                <div class="material-card small">
                                    <div class="material-card-content">
                                        <h4><?php echo $material['name']; ?></h4>
                                        <div class="material-price">₹<?php echo number_format($material['price']); ?></div>
                                        <a href="marketplace.php?material_id=<?php echo $material['id']; ?>" class="btn btn-small">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="marketplace-container">
                <div class="marketplace-sidebar">
                    <div class="search-filter-card">
                        <h3>Search & Filters</h3>
                        <form method="GET" action="" class="marketplace-filter-form">
                            <div class="form-group">
                                <label for="search">Search Materials</label>
                                <div class="search-input">
                                    <input type="text" id="search" name="search" placeholder="Search for materials..." value="<?php echo htmlspecialchars($search_term); ?>">
                                    <button type="submit" class="search-button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select id="category" name="category">
                                    <?php foreach ($categories as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($category == $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="max_price">Maximum Price (₹)</label>
                                <input type="range" id="max_price" name="max_price" min="0" max="<?php echo $remaining_budget; ?>" step="1000" value="<?php echo $max_price; ?>">
                                <div class="range-values">
                                    <span>₹0</span>
                                    <span id="priceValue">₹<?php echo number_format($max_price); ?></span>
                                    <span>₹<?php echo number_format($remaining_budget); ?></span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="marketplace.php" class="btn btn-secondary">Reset</a>
                        </form>
                    </div>
                </div>
                
                <div class="marketplace-content">
                    <?php if (empty($filtered_materials)): ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <p>No materials found matching your criteria.</p>
                            <p>Try adjusting your filters or search terms.</p>
                        </div>
                    <?php else: ?>
                        <div class="marketplace-grid">
                            <?php foreach ($filtered_materials as $material): ?>
                                <div class="material-card">
                                    <div class="material-category-badge"><?php echo $categories[$material['category']]; ?></div>
                                    <div class="material-card-content">
                                        <h3><?php echo $material['name']; ?></h3>
                                        <p class="material-description"><?php echo $material['description']; ?></p>
                                        
                                        <div class="material-info-grid">
                                            <div class="material-price">
                                                <span class="price">₹<?php echo number_format($material['price']); ?></span>
                                                <span class="unit">per <?php echo $material['unit']; ?></span>
                                            </div>
                                            
                                            <div class="material-supplier">
                                                <div class="supplier-name"><?php echo $material['supplier']; ?></div>
                                                <div class="supplier-location">
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo $material['location']; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="star-rating">
                                            <?php
                                                $rating = $material['rating'];
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } elseif ($i - 0.5 <= $rating) {
                                                        echo '<i class="fas fa-star-half-alt"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                            ?>
                                            <span>(<?php echo $rating; ?>)</span>
                                        </div>
                                        
                                        <div class="material-actions">
                                            <a href="marketplace.php?material_id=<?php echo $material['id']; ?>" class="btn btn-primary">View Details</a>
                                        </div>
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
        // Update price range display
        const priceRange = document.getElementById('max_price');
        const priceValue = document.getElementById('priceValue');
        
        if (priceRange && priceValue) {
            priceRange.addEventListener('input', function() {
                priceValue.textContent = '₹' + Number(this.value).toLocaleString();
            });
        }
    </script>
    
    <script src="js/script.js"></script>
</body>
</html>