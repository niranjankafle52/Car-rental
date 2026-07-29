<?php
session_start();

//Page Title
$pageTitle = 'Cars';

//Includes
include 'connect.php';
include 'Includes/functions/functions.php'; 
include 'Includes/templates/header.php';

//Check If user is already logged in
if (isset($_SESSION['username_car_rental']) && isset($_SESSION['password_car_rental'])) {
?>
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cars</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i>
            Generate Report
        </a>
    </div>

    <!-- ADD NEW CAR SUBMITTED -->
    <?php
    if (isset($_POST['add_car_sbmt']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $car_brand = test_input($_POST['car_brand']);
        $car_type = test_input($_POST['car_type']);
        $car_color = test_input($_POST['car_color']);
        $car_model = test_input($_POST['car_model']);
        $car_description = test_input($_POST['car_description']);

        try {
            $stmt = $con->prepare("INSERT INTO cars(brand_id,type_id,color,model,description) VALUES(?,?,?,?,?)");
            $stmt->execute(array($car_brand, $car_type, $car_color, $car_model, $car_description));
            echo "<div class='alert alert-success'>New Car has been inserted successfully</div>";
        } catch(Exception $e) {
            echo "<div class='alert alert-danger'>Error occurred: " . $e->getMessage() . "</div>";
        }
    }

    if (isset($_POST['delete_type_sbmt']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $type_id = $_POST['type_id'];
        try {
            $stmt = $con->prepare("DELETE FROM car_types WHERE type_id = ?");
            $stmt->execute(array($type_id));
            echo "<div class='alert alert-success'>Car Type has been deleted successfully</div>";
        } catch(Exception $e) {
            echo "<div class='alert alert-danger'>Error occurred: " . $e->getMessage() . "</div>";
        }
    }

    // Fetch Cars, Brands, Types
    $stmt = $con->prepare("SELECT * FROM cars");
    $stmt->execute();
    $rows_cars = $stmt->fetchAll();

    $stmt = $con->prepare("SELECT * FROM car_brands");
    $stmt->execute();
    $rows_brands = $stmt->fetchAll(); 

    $stmt = $con->prepare("SELECT * FROM car_types");
    $stmt->execute();
    $rows_types = $stmt->fetchAll(); 

    // Search and Sorting Logic
    $search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';

    if (!empty($search)) {
        $rows_cars = array_filter($rows_cars, function ($car) use ($search) {
            return stripos($car['car_name'], $search) !== false || stripos($car['model'], $search) !== false;
        });
    }

    if (!empty($sort)) {
        switch ($sort) {
            case 'price_asc':
                usort($rows_cars, fn($a, $b) => $a['price'] <=> $b['price']);
                break;
            case 'price_desc':
                usort($rows_cars, fn($a, $b) => $b['price'] <=> $a['price']);
                break;
            case 'model_asc':
                usort($rows_cars, fn($a, $b) => strcmp($a['model'], $b['model']));
                break;
            case 'model_desc':
                usort($rows_cars, fn($a, $b) => strcmp($b['model'], $a['model']));
                break;
        }
    }
    ?>

    <!--  Search and Sort Form -->
    <form method="GET" action="cars.php" class="form-inline mb-4">
        <input type="text" name="search" class="form-control mr-2" placeholder="Search by name or model" value="<?php echo htmlspecialchars($search); ?>">
        <select name="sort" class="form-control mr-2">
            <option value="">-- Sort By --</option>
            <option value="price_asc" <?php if ($sort == 'price_asc') echo 'selected'; ?>>Price (Low to High)</option>
            <option value="price_desc" <?php if ($sort == 'price_desc') echo 'selected'; ?>>Price (High to Low)</option>
            <option value="model_asc" <?php if ($sort == 'model_asc') echo 'selected'; ?>>Model (A-Z)</option>
            <option value="model_desc" <?php if ($sort == 'model_desc') echo 'selected'; ?>>Model (Z-A)</option>
        </select>
        <button type="submit" class="btn btn-primary">Apply</button>
    </form>

    <!-- Cars Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Cars</h6>
        </div>
        <div class="card-body">

            <!-- Existing Add Car Button/Modal Code Here -->

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Car ID</th>
                            <th>Car Name</th>
                            <th>Brand</th>
                            <th>Car Type</th>
                            <th>Color</th>
                            <th>Model</th>
                            <th style="width:30%">Description</th>
                            <th>Manage</th>
                        </tr>
                    </thead> 
                    <tbody>
                        <?php foreach ($rows_cars as $car): ?>
                            <tr>
                                <td><?php echo $car['id']; ?></td>
                                <td><?php echo htmlspecialchars($car['car_name']); ?></td>
                                <td><?php echo $car['brand_id']; ?></td>
                                <td><?php echo $car['type_id']; ?></td>
                                <td><?php echo $car['color']; ?></td>
                                <td><?php echo $car['model']; ?></td>
                                <td><?php echo $car['description']; ?></td>
                                <td>
                                    <ul>
                                        <li class="list-inline-item" data-toggle="tooltip" title="Edit">
                                            <button class="btn btn-success btn-sm rounded-0" type="button" data-toggle="modal"><i class="fa fa-edit"></i></button>
                                        </li>
                                        <li class="list-inline-item" data-toggle="tooltip" title="Delete">
                                            <button class="btn btn-danger btn-sm rounded-0" type="button" data-toggle="modal" data-target="#delete_<?php echo $car['id']; ?>"><i class="fa fa-trash"></i></button>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="delete_<?php echo $car['id']; ?>" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Delete Car</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">Are you sure you want to delete this Car?</div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="button" data-id="<?php echo $car['id']; ?>" class="btn btn-danger delete_car_bttn">Delete</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php
    include 'Includes/templates/footer.php';
} else {
    header('Location: index.php');
    exit();
}
?>

<script>
new Vue({
    el: "#add_new_car",
    data: {
        car_color: '',
        car_model: '',
        car_description: ''
    },
    methods: {
        checkForm: function(event) {
            if (this.car_color && this.car_model && this.car_description) {
                return true;
            }
            if (!this.car_color) this.car_color = null;
            if (!this.car_model) this.car_model = null;
            if (!this.car_description) this.car_description = null;
            event.preventDefault();
        }
    }
});
</script>
