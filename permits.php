<?php
session_start();
include("includes/config.php");
include("includes/functions.php");

// Your function here
function getTelanganaDistrictContact($plot_address, $district_contacts) {
    $address_parts = explode(',', strtolower($plot_address));
    $address_parts = array_map('trim', $address_parts);
    $known_districts = array_keys($district_contacts);

    foreach ($address_parts as $part) {
        foreach ($known_districts as $district) {
            if (strpos($part, strtolower($district)) !== false) {
                return $district_contacts[$district]; // Return full array with 'municipal' key
            }
        }
    }

    // Fallback with proper 'municipal' key structure
    return [
        'municipal' => [
            'name' => 'Telangana State Municipal Authority',
            'contact' => '+91 040-12345678',
            'website' => 'https://telangana.gov.in',
            'address' => 'Telangana Secretariat, Hyderabad - 500022'
        ]
    ];
}


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

// Extract location from address
$address = $building_details['plot_address'];
$location_parts = array_map('trim', explode(',', $address));

$mandal = '';
$district = '';
$state = '';

// We expect at least Mandal, District, State to be last 3 parts
$parts_count = count($location_parts);
if ($parts_count >= 3) {
    $mandal = $location_parts[$parts_count - 3];
    $district = $location_parts[$parts_count - 2];
    $state = $location_parts[$parts_count - 1];
}

// Fallback defaults
if (empty($state) || stripos($state, 'telangana') === false) {
    $state = 'Telangana';
}
if (empty($district)) {
    $district = 'Hyderabad';
}


// Default to Telangana if no state found
if (empty($state) || stripos($state, 'telangana') === false) {
    $state = 'Telangana';
}

// Define authority contact information for all Telangana districts
$authority_contacts = [
    'Adilabad' => [
        'municipal' => [
            'name' => 'Adilabad Municipality',
            'contact' => '+91 08732-226204',
            'website' => 'https://adilabad.telangana.gov.in',
            'address' => 'Municipal Office, Adilabad - 504001'
        ]
    ],
    'Mancherial' => [
        'municipal' => [
            'name' => 'Mancherial Municipality',
            'contact' => '+91 08736-252622',
            'website' => 'https://mancherial.telangana.gov.in',
            'address' => 'Municipal Office, Mancherial - 504208'
        ]
    ],
    'Nirmal' => [
        'municipal' => [
            'name' => 'Nirmal Municipality',
            'contact' => '+91 08734-232479',
            'website' => 'https://nirmal.telangana.gov.in',
            'address' => 'Municipal Office, Nirmal - 504106'
        ]
    ],
    'Nizamabad' => [
        'municipal' => [
            'name' => 'Nizamabad Municipal Corporation',
            'contact' => '+91 08462-221012',
            'website' => 'https://nizamabad.telangana.gov.in',
            'address' => 'Municipal Office, Nizamabad - 503001'
        ]
    ],
    'Kamareddy' => [
        'municipal' => [
            'name' => 'Kamareddy Municipality',
            'contact' => '+91 08468-222722',
            'website' => 'https://kamareddy.telangana.gov.in',
            'address' => 'Municipal Office, Kamareddy - 503111'
        ]
    ],
    'Jagtial' => [
        'municipal' => [
            'name' => 'Jagtial Municipality',
            'contact' => '+91 08724-247074',
            'website' => 'https://jagtial.telangana.gov.in',
            'address' => 'Municipal Office, Jagtial - 505327'
        ]
    ],
    'Karimnagar' => [
        'municipal' => [
            'name' => 'Karimnagar Municipal Corporation',
            'contact' => '+91 0878-2243535',
            'website' => 'https://karimnagarcorporation.telangana.gov.in',
            'address' => 'Municipal Corporation Building, Karimnagar - 505001'
        ]
    ],
    'Peddapalli' => [
        'municipal' => [
            'name' => 'Peddapalli Municipality',
            'contact' => '+91 08728-222444',
            'website' => 'https://peddapalli.telangana.gov.in',
            'address' => 'Municipal Office, Peddapalli - 505172'
        ]
    ],
    'Warangal Urban' => [
        'municipal' => [
            'name' => 'Greater Warangal Municipal Corporation',
            'contact' => '+91 0870-2501896',
            'website' => 'https://gwmc.gov.in',
            'address' => 'GWMC Office, Warangal - 506002'
        ]
    ],
    'Warangal Rural' => [
        'municipal' => [
            'name' => 'Wardhannapet Municipality',
            'contact' => '+91 0870-2886666',
            'website' => 'https://warangalrural.telangana.gov.in',
            'address' => 'Municipal Office, Wardhannapet - 506313'
        ]
    ],
    'Hanamkonda' => [
        'municipal' => [
            'name' => 'Hanamkonda District Administration',
            'contact' => '+91 0870-2444411',
            'website' => 'https://hanamkonda.telangana.gov.in',
            'address' => 'District Collectorate, Hanamkonda - 506001'
        ]
    ],
    'Bhupalpally' => [
        'municipal' => [
            'name' => 'Bhupalpally Municipality',
            'contact' => '+91 08713-245422',
            'website' => 'https://bhupalpally.telangana.gov.in',
            'address' => 'Municipal Office, Bhupalpally - 506169'
        ]
    ],
    'Mulugu' => [
        'municipal' => [
            'name' => 'Mulugu Municipality',
            'contact' => '+91 08715-250022',
            'website' => 'https://mulugu.telangana.gov.in',
            'address' => 'Municipal Office, Mulugu - 506343'
        ]
    ],
    'Khammam' => [
        'municipal' => [
            'name' => 'Khammam Municipal Corporation',
            'contact' => '+91 08742-241950',
            'website' => 'https://khammam.telangana.gov.in',
            'address' => 'Municipal Office, Khammam - 507001'
        ]
    ],
    'Kothagudem' => [
        'municipal' => [
            'name' => 'Kothagudem Municipality',
            'contact' => '+91 08744-244655',
            'website' => 'https://bhadradri.telangana.gov.in',
            'address' => 'Municipal Complex, Kothagudem - 507101'
        ]
    ],
    'Mahbubnagar' => [
        'municipal' => [
            'name' => 'Mahbubnagar Municipality',
            'contact' => '+91 08542-255367',
            'website' => 'https://mahbubnagar.telangana.gov.in',
            'address' => 'Municipal Office, Mahbubnagar - 509001'
        ]
    ],
    'Gadwal' => [
        'municipal' => [
            'name' => 'Gadwal Municipality',
            'contact' => '+91 08502-250755',
            'website' => 'https://jogulamba.telangana.gov.in',
            'address' => 'Municipal Office, Gadwal - 509125'
        ]
    ],
    'Wanaparthy' => [
        'municipal' => [
            'name' => 'Wanaparthy Municipality',
            'contact' => '+91 08504-222555',
            'website' => 'https://wanaparthy.telangana.gov.in',
            'address' => 'District Collectorate, Wanaparthy - 509103'
        ]
    ],
    'Nagarkurnool' => [
        'municipal' => [
            'name' => 'Nagarkurnool Municipality',
            'contact' => '+91 08506-222111',
            'website' => 'https://nagarkurnool.telangana.gov.in',
            'address' => 'Municipal Office, Nagarkurnool - 509209'
        ]
    ],
    'Nalgonda' => [
        'municipal' => [
            'name' => 'Nalgonda Municipality',
            'contact' => '+91 08682-222211',
            'website' => 'https://nalgonda.telangana.gov.in',
            'address' => 'Municipal Office, Nalgonda - 508001'
        ]
    ],
    'Suryapet' => [
        'municipal' => [
            'name' => 'Suryapet Municipality',
            'contact' => '+91 08684-222444',
            'website' => 'https://suryapet.telangana.gov.in',
            'address' => 'Municipal Office, Suryapet - 508213'
        ]
    ],
    'Yadadri Bhuvanagiri' => [
        'municipal' => [
            'name' => 'Bhuvanagiri Municipality',
            'contact' => '+91 08685-222000',
            'website' => 'https://yadadri.telangana.gov.in',
            'address' => 'Municipal Office, Bhuvanagiri - 508116'
        ]
    ],
    'Medak' => [
        'municipal' => [
            'name' => 'Medak Municipality',
            'contact' => '+91 08452-222122',
            'website' => 'https://medak.telangana.gov.in',
            'address' => 'Municipal Office, Medak - 502110'
        ]
    ],
    'Sangareddy' => [
        'municipal' => [
            'name' => 'Sangareddy Municipality',
            'contact' => '+91 08455-270789',
            'website' => 'https://sangareddy.telangana.gov.in',
            'address' => 'Municipal Office, Sangareddy - 502001'
        ]
    ],
    'Zaheerabad' => [
        'municipal' => [
            'name' => 'Zaheerabad Municipality',
            'contact' => '+91 08451-251300',
            'website' => 'https://sangareddy.telangana.gov.in',
            'address' => 'Municipal Office, Zaheerabad - 502220'
        ]
    ],
    'Vikarabad' => [
        'municipal' => [
            'name' => 'Vikarabad Municipality',
            'contact' => '+91 08416-254755',
            'website' => 'https://vikarabad.telangana.gov.in',
            'address' => 'Municipal Office, Vikarabad - 501101'
        ]
    ],
    'Chevella' => [
        'municipal' => [
            'name' => 'Chevella Municipality',
            'contact' => '+91 08417-222777',
            'website' => 'https://rangareddy.telangana.gov.in',
            'address' => 'Municipal Office, Chevella - 501503'
        ]
    ],
    'Ranga Reddy' => [
        'municipal' => [
            'name' => 'Ranga Reddy District Municipal Administration',
            'contact' => '+91 040-23114178',
            'website' => 'https://rangareddy.telangana.gov.in',
            'address' => 'District Collectorate, Ranga Reddy - 500032'
        ]
    ],
    'Medchal-Malkajgiri' => [
        'municipal' => [
            'name' => 'Medchal Municipality',
            'contact' => '+91 040-29702790',
            'website' => 'https://medchal.telangana.gov.in',
            'address' => 'Municipal Office, Medchal - 501401'
        ]
    ],
    'Hyderabad' => [
        'municipal' => [
            'name' => 'Greater Hyderabad Municipal Corporation (GHMC)',
            'contact' => '+91 040-23221978',
            'website' => 'https://www.ghmc.gov.in',
            'address' => 'GHMC Head Office, Hyderabad - 500063'
        ]
    ]
];




// Get the appropriate authority based on district
$local_authority = getTelanganaDistrictContact($address, $authority_contacts);


// Define common water board details
$water_board = [
    'name' => 'Telangana State Water Board',    
    'contact' => '+91 040-23442844',
    'website' => 'https://www.hyderabadwater.gov.in',
    'address' => 'Water Board Office, ' . $district . ' District'
];

// If Hyderabad, use HMWSSB details
if ($district === 'Hyderabad' || $district === 'Ranga Reddy' || $district === 'Medchal–Malkajgiri') {
    $water_board = [
        'name' => 'Hyderabad Metropolitan Water Supply and Sewerage Board',
        'contact' => '+91 040-23442844',
        'website' => 'https://www.hyderabadwater.gov.in',
        'address' => 'HMWSSB Head Office, Khairatabad, Hyderabad - 500004'
    ];
}

// Define permits required for construction
$permits = [
    [
        'id' => 1,
        'name' => 'Building Plan Approval',
        'authority' => $local_authority['municipal']['name'],
        'contact' => $local_authority['municipal']['contact'],
        'website' => $local_authority['municipal']['website'],
        'office_address' => $local_authority['municipal']['address'],
        'description' => 'Approval of building plans is mandatory before starting construction. This ensures that your building design complies with local building codes and zoning regulations.',
        'requirements' => [
            'Land ownership documents (Sale Deed/Gift Deed/Partition Deed)',
            'Up-to-date tax receipts',
            'Copy of approved layout plan',
            'Building plans prepared by licensed architect (5 copies)',
            'Structural stability certificate',
            'NOC from Airport Authority (if within airport zone)',
            'Soil test report',
            'Application form with prescribed fee'
        ],
        'process' => [
            'Submit application and documents at Municipal Corporation office or online portal',
            'Pay the processing fee',
            'Verification of documents by officials',
            'Site inspection by Town Planning staff',
            'Technical approval of plans',
            'Issuance of building permit'
        ],
        'timeline' => '15-30 days',
        'fees' => 'Varies based on plot size and building type (₹50-150 per sq. meter)'
    ],
    [
        'id' => 2,
        'name' => 'Water Connection Approval',
        'authority' => $water_board['name'],
        'contact' => $water_board['contact'],
        'website' => $water_board['website'],
        'office_address' => $water_board['address'],
        'description' => 'Permission to connect your building to the municipal water supply and sewerage system.',
        'requirements' => [
            'Copy of approved building plan',
            'Property tax receipt',
            'Land ownership documents',
            'Identity proof of owner',
            'Application form with prescribed fee'
        ],
        'process' => [
            'Submit application at Water Board office or online portal',
            'Pay the processing fee',
            'Site inspection by officials',
            'Feasibility assessment',
            'Payment of connection charges',
            'Installation of connection'
        ],
        'timeline' => '7-15 days',
        'fees' => 'Connection charges: ₹5,000-15,000 depending on pipe size and distance'
    ],
    [
        'id' => 3,
        'name' => 'Electricity Connection',
        'authority' => 'Telangana State Southern Power Distribution Company Ltd. (TSSPDCL)',
        'contact' => '+91 040-23431003',
        'website' => 'https://www.tssouthernpower.com',
        'office_address' => 'TSSPDCL Corporate Office, Mint Compound, Hyderabad - 500063',
        'description' => 'Permission and connection for electricity supply to your building.',
        'requirements' => [
            'Copy of approved building plan',
            'Land ownership documents',
            'Identity proof of owner',
            'NOC from Municipal Corporation',
            'Application form with prescribed fee'
        ],
        'process' => [
            'Submit application at TSSPDCL office or online portal',
            'Pay the processing fee',
            'Technical feasibility check',
            'Load sanction approval',
            'Payment of connection charges',
            'Installation of meter and connection'
        ],
        'timeline' => '7-15 days',
        'fees' => 'Connection charges: ₹3,000-25,000 depending on load requirement'
    ],
    [
        'id' => 4,
        'name' => 'Environmental Clearance',
        'authority' => 'Telangana State Pollution Control Board (TSPCB)',
        'contact' => '+91 040-23887500',
        'website' => 'https://tspcb.cgg.gov.in',
        'office_address' => 'TSPCB, Paryavaran Bhawan, A-III, Industrial Estate, Sanath Nagar, Hyderabad - 500018',
        'description' => 'Required for large projects to ensure environmental compliance. Mandatory for buildings with built-up area more than 20,000 sq. meters.',
        'requirements' => [
            'Environmental Impact Assessment Report',
            'Building plan documents',
            'Land documents',
            'Project report',
            'NOC from various departments',
            'Application form with prescribed fee'
        ],
        'process' => [
            'Submit application to TSPCB',
            'Scrutiny of documents',
            'Site inspection',
            'Committee review',
            'Public hearing (for large projects)',
            'Issuance of clearance'
        ],
        'timeline' => '60-90 days',
        'fees' => 'Processing fee: ₹25,000-₹1,00,000 based on project size'
    ],
    [
        'id' => 5,
        'name' => 'Fire Safety NOC',
        'authority' => 'Telangana State Disaster Response & Fire Services Department',
        'contact' => '+91 040-23442944',
        'website' => 'https://fireservices.telangana.gov.in',
        'office_address' => 'Fire Services Department, 1st Floor, DGP Office Complex, Lakdikapool, Hyderabad - 500004',
        'description' => 'No Objection Certificate for fire safety compliance. Mandatory for buildings above 15 meters in height or with multiple units.',
        'requirements' => [
            'Building plans showing fire safety installations',
            'Fire safety system drawings',
            'Copy of approved building plan',
            'Technical specifications of fire safety equipment',
            'Application form with prescribed fee'
        ],
        'process' => [
            'Submit application to Fire Services Department',
            'Scrutiny of fire safety plans',
            'Site inspection',
            'Verification of fire safety installations',
            'Issuance of provisional NOC',
            'Final NOC after completion of construction'
        ],
        'timeline' => '15-30 days',
        'fees' => 'Processing fee: ₹10,000-₹30,000 based on building height and area'
    ]
];

// Get selected permit details
$selected_permit = null;
if (isset($_GET['permit_id']) && is_numeric($_GET['permit_id'])) {
    $permit_id = $_GET['permit_id'];
    foreach ($permits as $permit) {
        if ($permit['id'] == $permit_id) {
            $selected_permit = $permit;
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
    <title>Legal Permits - Home Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Legal Permits Guide</h1>
            <p>Essential permits and approvals required for construction in <?php echo $district; ?>, <?php echo $state; ?></p>
            <div class="budget-pill">
                <span>Available Budget: </span>
                <strong>₹<?php echo number_format($remaining_budget); ?></strong>
            </div>
        </div>
        
        <?php if ($selected_permit): ?>
            <div class="permit-details-container">
                <div class="permit-details-header">
                    <h2><?php echo $selected_permit['name']; ?></h2>
                    <a href="permits.php" class="btn btn-secondary">Back to Permits List</a>
                </div>
                
                <div class="permit-details-grid">
                    <div class="permit-details-card">
                        <div class="permit-description">
                            <p><?php echo $selected_permit['description']; ?></p>
                        </div>
                        
                        <div class="permit-authority">
                            <h3>Issuing Authority</h3>
                            <p><strong><?php echo $selected_permit['authority']; ?></strong></p>
                            <div class="authority-details">
                                <div class="authority-contact">
                                    <p><i class="fas fa-phone"></i> <?php echo $selected_permit['contact']; ?></p>
                                    <p><i class="fas fa-globe"></i> <a href="<?php echo $selected_permit['website']; ?>" target="_blank"><?php echo $selected_permit['website']; ?></a></p>
                                </div>
                                <div class="authority-address">
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $selected_permit['office_address']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="permit-timeline">
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Processing Time</h4>
                                    <p><?php echo $selected_permit['timeline']; ?></p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Approximate Fees</h4>
                                    <p><?php echo $selected_permit['fees']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="permit-requirements">
                            <h3>Required Documents</h3>
                            <ul>
                                <?php foreach ($selected_permit['requirements'] as $requirement): ?>
                                    <li><i class="fas fa-file-alt"></i> <?php echo $requirement; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="permit-process">
                            <h3>Application Process</h3>
                            <ol class="process-steps">
                                <?php foreach ($selected_permit['process'] as $index => $step): ?>
                                    <li>
                                        <div class="step-number"><?php echo $index + 1; ?></div>
                                        <div class="step-content"><?php echo $step; ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                        
                        <div class="permit-actions">
                            <?php if ($selected_permit['website']): ?>
                                <a href="<?php echo $selected_permit['website']; ?>" target="_blank" class="btn btn-primary">Visit Official Website</a>
                            <?php endif; ?>
                            
                            <a href="tel:<?php echo str_replace(' ', '', $selected_permit['contact']); ?>" class="btn btn-secondary">Call Authority</a>
                            
                            <a href="progress_log.php" class="btn btn-secondary">Track in Budget</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="permits-container">
                <div class="permits-grid">
                    <?php foreach ($permits as $permit): ?>
                        <div class="permit-card">
                            <h3><?php echo $permit['name']; ?></h3>
                            <p class="permit-brief"><?php echo substr($permit['description'], 0, 120); ?>...</p>
                            
                            <div class="permit-meta">
                                <div class="meta-item">
                                    <i class="fas fa-building"></i>
                                    <span><?php echo $permit['authority']; ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $permit['timeline']; ?></span>
                                </div>
                            </div>
                            
                            <a href="permits.php?permit_id=<?php echo $permit['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="permits-info">
                    <div class="info-card">
                        <h3>Why Permits Matter</h3>
                        <p>Building without proper permits can result in:</p>
                        <ul>
                            <li>Heavy fines and penalties</li>
                            <li>Demolition orders for unauthorized construction</li>
                            <li>Difficulty selling the property in the future</li>
                            <li>Insurance issues and claim denials</li>
                            <li>Safety risks and liability concerns</li>
                        </ul>
                        <p>Always obtain all required permits before starting construction!</p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Need Help?</h3>
                        <p>If navigating permits seems overwhelming, consider:</p>
                        <ul>
                            <li>Hiring an architect familiar with local regulations</li>
                            <li>Consulting with a construction lawyer</li>
                            <li>Working with an experienced contractor</li>
                            <li>Using permit expediting services</li>
                        </ul>
                        <p>Visit our <a href="workers.php">Workers page</a> to find professionals who can help!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <script src="js/script.js"></script>
</body>
</html>