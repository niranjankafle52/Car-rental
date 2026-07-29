<<?php
session_start();
error_reporting(0);
include('includes/config.php');
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
<script>document.documentElement.classList.add('js-anim');</script>
<title>Car Rental Portal | Page Details</title>

<!-- Bootstrap -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">

<!-- Custom Style -->
<link rel="stylesheet" href="assets/css/style.css" type="text/css">

<!-- FontAwesome -->
<link rel="stylesheet" href="assets/css/font-awesome.min.css">

<!-- Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/modern-theme.css" type="text/css">
</head>
<body>

<!-- Header -->
<?php include('includes/header.php'); ?>

<?php 
$pagetype = $_GET['type'];
$sql = "SELECT type, detail, PageName FROM tblpages WHERE type = :pagetype";
$query = $dbh->prepare($sql);
$query->bindParam(':pagetype', $pagetype, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() > 0) {
  foreach ($results as $result) {
?>

<!-- Page Header -->
<section class="page-header aboutus_page">
  <div class="container">
    <div class="page-header_wrap">
      <div class="page-heading">
        <h1><?php echo htmlentities($result->PageName); ?></h1>
      </div>
      <ul class="coustom-breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li><?php echo htmlentities($result->PageName); ?></li>
      </ul>
    </div>
  </div>
  <div class="dark-overlay"></div>
</section>

<!-- About Us Content -->
<section class="about_us section-padding">
  <div class="container">
    <div class="section-header text-center reveal">
      <h2 class="mb-4"><?php echo htmlentities($result->PageName); ?></h2>
      <p class="lead text-muted">
        We offer a varied fleet of cars, ranging from compact models. All our vehicles are equipped with air conditioning, power steering, and electric windows. Each car is bought and maintained at official dealerships to ensure reliability and safety.
        Automatic transmission cars are available in every booking class. Since we are not affiliated with any specific automaker, we can offer a wide range of vehicle makes and models to suit your needs.
        Our mission is to be recognized as a global leader in car rental—serving both public and private sectors by providing the most efficient and customer-focused cab rental solutions.
      </p>
    </div>
  </div>
</section>

<?php 
  } // end foreach
} // end if
?>

<!-- Footer -->
<?php include('includes/footer.php'); ?>

<!-- Back to Top -->
<div id="back-top" class="back-top"> 
  <a href="#top"><i class="fa fa-angle-up" aria-hidden="true"></i></a> 
</div>

<!-- Login/Registration/Forgot -->
<?php include('includes/login.php'); ?>
<?php include('includes/registration.php'); ?>
<?php include('includes/forgotpassword.php'); ?>

<!-- Scripts --> 
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/interface.js"></script>
<script src="assets/js/bootstrap-slider.min.js"></script>
<script src="assets/js/slick.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/modern-interactions.js"></script>

</body>
</html>
