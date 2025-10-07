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

// Check if building details already exist
$building_details = getBuildingDetailsByUserId($user_id);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plot_area = $_POST['plot_area'];
    $plot_address = $_POST['plot_address'];
    $floors = $_POST['floors'];
    $building_type = $_POST['building_type'];
    $budget = $_POST['budget'];
    
    // Validate inputs
    if (empty($plot_area) || empty($plot_address) || empty($floors) || empty($building_type) || empty($budget)) {
        $error = "Please fill in all fields";
    } elseif (!is_numeric($plot_area) || $plot_area <= 0) {
        $error = "Plot area must be a positive number";
    } elseif (!is_numeric($floors) || $floors <= 0) {
        $error = "Number of floors must be a positive number";
    } elseif (!is_numeric($budget) || $budget <= 0) {
        $error = "Budget must be a positive number";
    } else {
        // Save or update building details
        if ($building_details) {
            // Update existing details
            $sql = "UPDATE building_details SET plot_area = ?, plot_address = ?, floors = ?, building_type = ?, budget = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dsisdi", $plot_area, $plot_address, $floors, $building_type, $budget, $user_id);
        } else {
            // Insert new details
            $sql = "INSERT INTO building_details (user_id, plot_area, plot_address, floors, building_type, budget) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idsisi", $user_id, $plot_area, $plot_address, $floors, $building_type, $budget);
        }
        
        if ($stmt->execute()) {
            $success = "Building details saved successfully!";
            
            // Redirect to home page after short delay
            header("refresh:2;url=index.php");
        } else {
            $error = "Failed to save building details: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Details - Home Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Building Details</h1>
            <p>Please provide information about your construction project</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="plot_area">Plot Area (in sq yards)</label>
                    <input type="number" id="plot_area" name="plot_area" value="<?php echo isset($building_details['plot_area']) ? $building_details['plot_area'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="plot_address">Plot Address</label>
                    <!--<textarea id="plot_address" name="plot_address" placeholder="General area, district, state" required><?php echo isset($building_details['plot_address']) ? $building_details['plot_address'] : ''; ?></textarea>-->
                    <input type="text" id="plot_address" name="plot_address" required placeholder="Start typing area, mandal, district, state" value="<?php echo isset($building_details['plot_address']) ? $building_details['plot_address'] : ''; ?>">

                </div>
                
                <div class="form-group">
                    <label for="floors">Number of Floors</label>
                    <input type="number" id="floors" name="floors" min="1" value="<?php echo isset($building_details['floors']) ? $building_details['floors'] : '1'; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="building_type">Building Type</label>
                    <select id="building_type" name="building_type" required>
                        <option value="">-- Select Building Type --</option>
                        <option value="residential" <?php echo (isset($building_details['building_type']) && $building_details['building_type'] == 'residential') ? 'selected' : ''; ?>>Residential</option>
                        <option value="villa" <?php echo (isset($building_details['building_type']) && $building_details['building_type'] == 'villa') ? 'selected' : ''; ?>>Villa</option>
                        <option value="apartment" <?php echo (isset($building_details['building_type']) && $building_details['building_type'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                        <option value="duplex" <?php echo (isset($building_details['building_type']) && $building_details['building_type'] == 'duplex') ? 'selected' : ''; ?>>Duplex</option>
                        <option value="commercial" <?php echo (isset($building_details['building_type']) && $building_details['building_type'] == 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                        <option value="townhouse" <?php echo (isset($building_details['building_type']) && $building_details['building_type'] == 'townhouse') ? 'selected' : ''; ?>>Town House</option>
                        <option value="cottage" <?php echo (isset($building_details['building_type']) && $building_details['building_type'] == 'cottage') ? 'selected' : ''; ?>>Cottage</option>
                        <option value="bungalow" <?php echo (isset($building_details['building_type']) && $building_details['building_type'] == 'bungalow') ? 'selected' : ''; ?>>Bungalow</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="budget">Approximate Budget (₹)</label>
                    <input type="number" id="budget" name="budget" value="<?php echo isset($building_details['budget']) ? $building_details['budget'] : ''; ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $building_details ? 'Update Details' : 'Save Details'; ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php include("includes/footer.php"); ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="js/script.js"></script>
</body>
</html>