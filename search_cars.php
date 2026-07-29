<?php
include "connect.php"; // Include your database connection

if (isset($_POST['search_term'])) {
    $searchTerm = $_POST['search_term'];

    $stmt = $con->prepare("SELECT * FROM cars
                           WHERE car_name LIKE :search OR brand_id LIKE :search OR type_id LIKE :search");
    $stmt->bindValue(':search', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $searchResults = $stmt->fetchAll();

    foreach ($searchResults as $car) {
        echo "<div class='itemListElement'>";
        echo "<div class='item_details'>";
        echo "<div>" . $car['car_name'] . "</div>";
        echo "<div class='item_select_part'>";
        echo "<div class='select_item_bttn'>";
        echo "<label class='item_label btn btn-secondary active'>";
        echo "<input type='radio' class='radio_car_select' name='selected_car' v-model='selected_car' value='" . $car['id'] . "'>Select";
        echo "</label>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
}
?>
