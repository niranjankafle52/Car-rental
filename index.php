<?php 
session_start();
include('includes/config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
?>


<!DOCTYPE HTML>
<html lang="en">
<head>
<script>document.documentElement.classList.add('js-anim');</script>
<title>Car Rental Portal</title>
<!--Bootstrap -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="assets/css/style.css" type="text/css">
<link rel="stylesheet" href="assets/css/owl.carousel.css" type="text/css">
<link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css">
<link href="assets/css/slick.css" rel="stylesheet">
<link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">
<link href="assets/css/font-awesome.min.css" rel="stylesheet">
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

<!-- Banners -->
<section id="banner" class="banner-section">
  <div class="container">
    <div class="div_zindex">
      <div class="row">
        <div class="col-md-7">
          <div class="banner_content">
            <span class="hero-eyebrow"><i class="fa fa-bolt" aria-hidden="true"></i> Premium Fleet &middot; Instant Booking</span>
            <h1>Drive Something <em>Extraordinary</em>, Every Time</h1>
            <p>A curated fleet of premium vehicles, transparent pricing and a booking experience built for people who expect more from a rental. Find your match in minutes.</p>
            <div class="hero-actions">
              <a href="car-listing.php" class="btn">Browse The Fleet</a>
              <a href="greedy-form.php" class="btn outline"><i class="fa fa-brain" aria-hidden="true"></i> Smart Car Finder</a>
            </div>
          </div>
        </div>
        <div class="col-md-5">
          <div class="hero-finder-card">
            <span class="fc-tag">Smart Car Finder</span>
            <h3>Let the algorithm pick your car</h3>
            <p>Tell us your dates, budget and preferences &mdash; our greedy-optimization engine scores every vehicle in the fleet and surfaces the best matches instantly.</p>
            <a href="greedy-form.php" class="btn"><i class="fa fa-magic" aria-hidden="true"></i> Find My Perfect Car</a>
            <div class="hero-finder-stats">
              <div><strong>10+</strong><span>Vehicles</span></div>
              <div><strong>24/7</strong><span>Support</span></div>
              <div><strong>4.9<i class="fa fa-star" aria-hidden="true" style="font-size:12px;"></i></strong><span>Rated</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /Banners -->


<!-- Resent Cat-->
<section class="section-padding gray-bg">
  <div class="container">
    <div class="section-header text-center reveal">
    <h2>Find the Best Car For You</h2><p>When choosing a new car, it's important to consider factors like performance, fuel efficiency, safety features, and technology. Depending on your needs, you might prioritize a fuel-efficient hybrid, a high-performance sports car, or a spacious family vehicle with advanced safety and tech features. New cars often come equipped with modern amenities such as infotainment systems, connectivity options, and driver-assistance features. Additionally, consider your budget, long-term maintenance costs, and the car's eco-friendliness, especially if you're interested in electric or hybrid options. Test-driving a few models will help ensure you find the best car that fits your lifestyle and preferences.</p>
    </div>

    <!-- Greedy Algorithm Feature Highlight -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="smart-finder-cta text-center reveal">
          <h3><i class="fa fa-brain" aria-hidden="true"></i> Try Our Smart Car Finder!</h3>
          <p>Our advanced greedy algorithm analyzes multiple criteria to find your perfect car match. Get personalized recommendations based on price, availability, preferences, and quality.</p>
          <a href="greedy-form.php" class="btn">
            <i class="fa fa-magic" aria-hidden="true"></i> Find My Perfect Car
          </a>
        </div>
      </div>
    </div>

    <div class="row"> 
      
      <!-- Nav tabs -->
      <div class="recent-tab">
        <ul class="nav nav-tabs" role="tablist">
          <li role="presentation" class="active"><a href="#resentnewcar" role="tab" data-toggle="tab">New Car</a></li>
        </ul>
      </div>
      <!-- Recently Listed New Cars -->
      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="resentnewcar">

<?php $sql = "SELECT tblvehicles.VehiclesTitle,tblbrands.BrandName,tblvehicles.PricePerDay,tblvehicles.FuelType,tblvehicles.ModelYear,tblvehicles.id,tblvehicles.SeatingCapacity,tblvehicles.VehiclesOverview,tblvehicles.Vimage1 from tblvehicles join tblbrands on tblbrands.id=tblvehicles.VehiclesBrand limit 9";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{  
?>  

<div class="col-list-3 reveal">
<div class="recent-car-list">
<div class="car-info-box"> <a href="vehical-details.php?vhid=<?php echo htmlentities($result->id);?>"><img src="admin/img/vehicleimages/<?php echo htmlentities($result->Vimage1);?>" class="img-responsive" alt="image"></a>
<ul>
<li><i class="fa fa-car" aria-hidden="true"></i><?php echo htmlentities($result->FuelType);?></li>
<li><i class="fa fa-calendar" aria-hidden="true"></i><?php echo htmlentities($result->ModelYear);?> Model</li>
<li><i class="fa fa-user" aria-hidden="true"></i><?php echo htmlentities($result->SeatingCapacity);?> seats</li>
</ul>
</div>
<div class="car-title-m">
<h6><a href="vehical-details.php?vhid=<?php echo htmlentities($result->id);?>"> <?php echo htmlentities($result->VehiclesTitle);?></a></h6>
<span class="price">NRS<?php echo htmlentities($result->PricePerDay);?> /Day</span> 
</div>
<div class="inventory_info_m">
<p><?php echo substr($result->VehiclesOverview,0,70);?></p>
</div>
</div>
</div>
<?php }}?>
       
      </div>
    </div>
  </div>
</section>
<!-- /Resent Cat --> 

<!-- Fun Facts-->
<section class="fun-facts-section">
  <div class="container div_zindex">
    <div class="row">
      <div class="col-lg-3 col-xs-6 col-sm-3">
        <div class="fun-facts-m reveal">
          <div class="cell">
            <h2><i class="fa fa-calendar" aria-hidden="true"></i><span class="count-up" data-count="40">0</span>+</h2>
            <p>Years In Business</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6 col-sm-3">
        <div class="fun-facts-m reveal">
          <div class="cell">
            <h2><i class="fa fa-car" aria-hidden="true"></i><span class="count-up" data-count="1200">0</span>+</h2>
            <p>New Cars For Sale</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6 col-sm-3">
        <div class="fun-facts-m reveal">
          <div class="cell">
            <h2><i class="fa fa-car" aria-hidden="true"></i><span class="count-up" data-count="1000">0</span>+</h2>
            <p>Used Cars For Sale</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6 col-sm-3">
        <div class="fun-facts-m reveal">
          <div class="cell">
            <h2><i class="fa fa-user-circle-o" aria-hidden="true"></i><span class="count-up" data-count="600">0</span>+</h2>
            <p>Satisfied Customers</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Dark Overlay-->
  <div class="dark-overlay"></div>
</section>
<!-- /Fun Facts--> 


<!--Testimonial -->
<section class="section-padding testimonial-section parallex-bg">
  <div class="container div_zindex">
    <div class="section-header white-text text-center reveal">
      <h2>Our Satisfied <span>Customers</span></h2>
    </div>
    <div class="row">
      <div id="testimonial-slider">
<?php 
$tid=1;
$sql = "SELECT tbltestimonial.Testimonial,tblusers.FullName from tbltestimonial join tblusers on tbltestimonial.UserEmail=tblusers.EmailId where tbltestimonial.status=:tid limit 4";
$query = $dbh -> prepare($sql);
$query->bindParam(':tid',$tid, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{  ?>


        <div class="testimonial-m">
 
          <div class="testimonial-content">
            <div class="testimonial-heading">
              <h5><?php echo htmlentities($result->FullName);?></h5>
            <p><?php echo htmlentities($result->Testimonial);?></p>
          </div>
        </div>
        </div>
        <?php }} ?>
        
       
  
      </div>
    </div>
  </div>
  <!-- Dark Overlay-->
  <div class="dark-overlay"></div>
</section>
<!-- /Testimonial--> 


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
<!--/Forgot-password-Form --> 

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

</body>

<!-- Mirrored from themes.webmasterdriver.net/carforyou/demo/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 16 Jun 2017 07:22:11 GMT -->
</html>