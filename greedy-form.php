<?php 
session_start();
include('includes/config.php');
include('greedy_algorithm.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize greedy algorithm
$greedyAlgorithm = new CarRentalGreedyAlgorithm($dbh);

// Process form submission
$recommendations = [];
$searchPerformed = false;
$algorithmExplanation = $greedyAlgorithm->getAlgorithmExplanation();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['find_optimal_cars'])) {
    $searchPerformed = true;
    
    // Get form data
    $fromDate = $_POST['from_date'];
    $toDate = $_POST['to_date'];
    $maxPrice = $_POST['max_price'] ?? 10000;
    $fuelType = $_POST['fuel_type'] ?? '';
    $seatingCapacity = $_POST['seating_capacity'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $minYear = $_POST['min_year'] ?? '';
    
    // Prepare criteria for greedy algorithm
    $criteria = [
        'max_price' => $maxPrice,
        'fuel_type' => $fuelType,
        'seating_capacity' => $seatingCapacity,
        'brand' => $brand,
        'min_year' => $minYear
    ];
    
    // Get optimal car recommendations
    $recommendations = $greedyAlgorithm->findOptimalCars($criteria, $fromDate, $toDate);
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
<script>document.documentElement.classList.add('js-anim');</script>
<title>Car Rental Portal | Greedy Algorithm Optimization</title>
<!--Bootstrap -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
<!--Custome Style -->
<link rel="stylesheet" href="assets/css/style.css" type="text/css">
<!--OWL Carousel slider-->
<link rel="stylesheet" href="assets/css/owl.carousel.css" type="text/css">
<link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css">
<!--slick-slider -->
<link href="assets/css/slick.css" rel="stylesheet">
<!--bootstrap-slider -->
<link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">
<!--FontAwesome Font Style -->
<link href="assets/css/font-awesome.min.css" rel="stylesheet">

<!-- Fav and touch icons -->
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/images/favicon-icon/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/images/favicon-icon/apple-touch-icon-114-precomposed.html">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/images/favicon-icon/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="assets/images/favicon-icon/apple-touch-icon-57-precomposed.png">
<link rel="shortcut icon" href="assets/images/favicon-icon/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/modern-theme.css" type="text/css">
</head>
<body>

<!--Header--> 
<?php include('includes/header.php');?>
<!-- /Header --> 

<!--Page Header-->
<section class="page-header listing_page">
  <div class="container">
    <div class="page-header_wrap">
      <div class="page-heading">
        <h1><i class="fa fa-brain" aria-hidden="true"></i> Greedy Algorithm Car Optimization</h1>
        <p>Find the best car matches using our intelligent greedy algorithm</p>
      </div>
      <ul class="coustom-breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li>Greedy Optimization</li>
      </ul>
    </div>
  </div>
  <!-- Dark Overlay-->
  <div class="dark-overlay"></div>
</section>
<!-- /Page Header--> 

<!-- Greedy Form Section -->
<section class="greedy-form-container">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="greedy-form reveal">
          <div class="text-center mb-4">
            <h2><i class="fa fa-cogs" aria-hidden="true"></i> Smart Car Finder</h2>
            <p class="text-muted">Our greedy algorithm analyzes multiple criteria to find your perfect car match</p>
          </div>


          <!-- Search Form -->
          <form method="POST" action="">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="from_date"><i class="fa fa-calendar" aria-hidden="true"></i> Pickup Date</label>
                  <input type="date" class="form-control" id="from_date" name="from_date" required 
                         value="<?php echo isset($_POST['from_date']) ? $_POST['from_date'] : ''; ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="to_date"><i class="fa fa-calendar" aria-hidden="true"></i> Return Date</label>
                  <input type="date" class="form-control" id="to_date" name="to_date" required
                         value="<?php echo isset($_POST['to_date']) ? $_POST['to_date'] : ''; ?>">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="max_price"><i class="fa fa-dollar" aria-hidden="true"></i> Maximum Price per Day (NRS)</label>
                  <input type="number" class="form-control" id="max_price" name="max_price" 
                         placeholder="10000" value="<?php echo isset($_POST['max_price']) ? $_POST['max_price'] : '10000'; ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fuel_type"><i class="fa fa-car" aria-hidden="true"></i> Preferred Fuel Type</label>
                  <select class="form-control" id="fuel_type" name="fuel_type">
                    <option value="">Any Fuel Type</option>
                    <option value="Petrol" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Petrol') ? 'selected' : ''; ?>>Petrol</option>
                    <option value="Diesel" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="seating_capacity"><i class="fa fa-users" aria-hidden="true"></i> Seating Capacity</label>
                  <select class="form-control" id="seating_capacity" name="seating_capacity">
                    <option value="">Any Capacity</option>
                    <option value="2" <?php echo (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '2') ? 'selected' : ''; ?>>2 Seats</option>
                    <option value="4" <?php echo (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '4') ? 'selected' : ''; ?>>4 Seats</option>
                    <option value="5" <?php echo (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '5') ? 'selected' : ''; ?>>5 Seats</option>
                    <option value="7" <?php echo (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '7') ? 'selected' : ''; ?>>7 Seats</option>
                    <option value="8" <?php echo (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '8') ? 'selected' : ''; ?>>8+ Seats</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="brand"><i class="fa fa-tag" aria-hidden="true"></i> Preferred Brand</label>
                  <select class="form-control" id="brand" name="brand">
                    <option value="">Any Brand</option>
                    <?php 
                    $sql = "SELECT * FROM tblbrands ORDER BY BrandName";
                    $query = $dbh->prepare($sql);
                    $query->execute();
                    $brands = $query->fetchAll(PDO::FETCH_OBJ);
                    foreach($brands as $brand) {
                        $selected = (isset($_POST['brand']) && $_POST['brand'] == $brand->BrandName) ? 'selected' : '';
                        echo "<option value='{$brand->BrandName}' {$selected}>{$brand->BrandName}</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="min_year"><i class="fa fa-calendar-o" aria-hidden="true"></i> Minimum Model Year</label>
                  <select class="form-control" id="min_year" name="min_year">
                    <option value="">Any Year</option>
                    <?php 
                    $currentYear = date('Y');
                    for($year = $currentYear; $year >= $currentYear - 10; $year--) {
                        $selected = (isset($_POST['min_year']) && $_POST['min_year'] == $year) ? 'selected' : '';
                        echo "<option value='{$year}' {$selected}>{$year}</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="submit" name="find_optimal_cars" class="btn btn-primary btn-block">
                    <i class="fa fa-search" aria-hidden="true"></i> Find Optimal Cars
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Results Section -->
<?php if ($searchPerformed): ?>
<section class="section-padding">
  <div class="container">
    <div class="section-header text-center">
      <h2><i class="fa fa-star" aria-hidden="true"></i> Greedy Algorithm Results</h2>
      <p>Top car recommendations based on your criteria</p>
    </div>

    <?php if (empty($recommendations)): ?>
      <div class="alert alert-warning text-center">
        <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> No Cars Found</h4>
        <p>No cars are available for the selected date range. Please try different dates or adjust your criteria.</p>
      </div>
    <?php else: ?>
      <div class="row">
        <?php foreach ($recommendations as $index => $car): ?>
          <div class="col-md-6 col-lg-4">
            <div class="recommendation-card reveal">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <h5><?php echo htmlentities($car['BrandName'] . ' ' . $car['VehiclesTitle']); ?></h5>
                <span class="score-badge">
                  Score: <?php echo $car['total_score']; ?>
                  <?php if ($index == 0): ?>
                    <span class="greedy-highlight">BEST</span>
                  <?php endif; ?>
                </span>
              </div>

              <div class="text-center mb-3">
                <img src="admin/img/vehicleimages/<?php echo htmlentities($car['Vimage1']); ?>" 
                     class="img-responsive" alt="Car Image" style="max-height: 150px; width: 100%; object-fit: cover;">
              </div>

              <div class="row text-center mb-3">
                <div class="col-4">
                  <small class="text-muted">Price</small><br>
                  <strong>NRS <?php echo number_format($car['PricePerDay']); ?></strong>
                </div>
                <div class="col-4">
                  <small class="text-muted">Seats</small><br>
                  <strong><?php echo $car['SeatingCapacity']; ?></strong>
                </div>
                <div class="col-4">
                  <small class="text-muted">Year</small><br>
                  <strong><?php echo $car['ModelYear']; ?></strong>
                </div>
              </div>

              <div class="score-breakdown">
                <h6><i class="fa fa-chart-bar" aria-hidden="true"></i> Score Breakdown</h6>
                <div class="score-item">
                  <span>Price Score:</span>
                  <span><?php echo round($car['score_breakdown']['price_score'], 1); ?>%</span>
                </div>
                <div class="score-item">
                  <span>Availability:</span>
                  <span><?php echo round($car['score_breakdown']['availability_score'], 1); ?>%</span>
                </div>
                <div class="score-item">
                  <span>Preferences:</span>
                  <span><?php echo round($car['score_breakdown']['preference_score'], 1); ?>%</span>
                </div>
                <div class="score-item">
                  <span>Quality:</span>
                  <span><?php echo round($car['score_breakdown']['quality_score'], 1); ?>%</span>
                </div>
              </div>

              <div class="mt-3">
                <a href="vehical-details.php?vhid=<?php echo $car['id']; ?>" 
                   class="btn btn-primary btn-sm btn-block">
                  <i class="fa fa-eye" aria-hidden="true"></i> View Details
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-4">
        <div class="alert alert-info">
          <h5><i class="fa fa-lightbulb-o" aria-hidden="true"></i> Algorithm Insight</h5>
          <p>The greedy algorithm selected these cars by making locally optimal choices at each step, 
             considering price, availability, your preferences, and vehicle quality to find the best overall matches.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!--Footer -->
<?php include('includes/footer.php');?>
<!-- /Footer--> 

<!--Back to top-->
<div id="back-top" class="back-top"> <a href="#top"><i class="fa fa-angle-up" aria-hidden="true"></i> </a> </div>
<!--/Back to top--> 

<!--Login-Form -->
<?php include('includes/login.php');?>
<!--/Login-Form --> 

<!--Register-Form -->
<?php include('includes/registration.php');?>
<!--/Register-Form --> 

<!--Forgot-password-Form -->
<?php include('includes/forgotpassword.php');?>

<!-- Scripts --> 
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/interface.js"></script>
<!--bootstrap-slider-JS-->
<script src="assets/js/bootstrap-slider.min.js"></script>
<!--Slider-JS-->
<script src="assets/js/slick.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/modern-interactions.js"></script>

<script>
// Form validation and enhancement
$(document).ready(function() {
    // Set minimum date to today
    var today = new Date().toISOString().split('T')[0];
    $('#from_date').attr('min', today);
    $('#to_date').attr('min', today);
    
    // Update return date minimum when pickup date changes
    $('#from_date').on('change', function() {
        $('#to_date').attr('min', $(this).val());
    });
    
    // Form submission animation
    $('form').on('submit', function() {
        $('button[type="submit"]').html('<i class="fa fa-spinner fa-spin"></i> Analyzing...');
    });
});
</script>

</body>
</html>
