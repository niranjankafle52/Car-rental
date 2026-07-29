<?php
/**
 * Greedy Algorithm API Endpoint
 * Provides RESTful API access to the greedy car rental optimization algorithm
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include('includes/config.php');
include('greedy_algorithm.php');

// Initialize greedy algorithm
$greedyAlgorithm = new CarRentalGreedyAlgorithm($dbh);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            handlePostRequest($greedyAlgorithm);
            break;
        case 'GET':
            handleGetRequest($greedyAlgorithm);
            break;
        default:
            throw new Exception('Method not allowed', 405);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'code' => $e->getCode() ?: 500
    ]);
}

/**
 * Handle POST requests for car optimization
 */
function handlePostRequest($greedyAlgorithm) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input', 400);
    }
    
    // Validate required fields
    $requiredFields = ['from_date', 'to_date'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field", 400);
        }
    }
    
    // Prepare criteria
    $criteria = [
        'max_price' => $input['max_price'] ?? 10000,
        'fuel_type' => $input['fuel_type'] ?? '',
        'seating_capacity' => $input['seating_capacity'] ?? '',
        'brand' => $input['brand'] ?? '',
        'min_year' => $input['min_year'] ?? ''
    ];
    
    // Get recommendations
    $recommendations = $greedyAlgorithm->findOptimalCars(
        $criteria, 
        $input['from_date'], 
        $input['to_date']
    );
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'recommendations' => $recommendations,
            'total_found' => count($recommendations),
            'criteria_used' => $criteria,
            'search_dates' => [
                'from' => $input['from_date'],
                'to' => $input['to_date']
            ]
        ],
        'algorithm_info' => $greedyAlgorithm->getAlgorithmExplanation()
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * Handle GET requests for algorithm information
 */
function handleGetRequest($greedyAlgorithm) {
    $action = $_GET['action'] ?? 'info';
    
    switch ($action) {
        case 'info':
            $response = [
                'success' => true,
                'data' => $greedyAlgorithm->getAlgorithmExplanation()
            ];
            break;
            
        case 'brands':
            // Get available brands
            global $dbh;
            $sql = "SELECT id, BrandName FROM tblbrands ORDER BY BrandName";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'success' => true,
                'data' => $brands
            ];
            break;
            
        case 'stats':
            // Get algorithm statistics
            global $dbh;
            $sql = "SELECT COUNT(*) as total_cars FROM tblvehicles";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            $totalCars = $stmt->fetch(PDO::FETCH_ASSOC)['total_cars'];
            
            $sql = "SELECT COUNT(*) as total_bookings FROM tblbooking WHERE Status IN (0, 1)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            $totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];
            
            $response = [
                'success' => true,
                'data' => [
                    'total_cars' => $totalCars,
                    'total_bookings' => $totalBookings,
                    'algorithm_version' => '1.0',
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ];
            break;
            
        default:
            throw new Exception('Invalid action', 400);
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
}
?>
