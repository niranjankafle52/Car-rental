<?php
// ---------------------------------------------
// Content-Based Filtering Algorithm (Recommendation)
// ---------------------------------------------
function getRecommendedCars($selectedCar, $allCars) {
    $recommendations = [];

    foreach ($allCars as $car) {
        // Skip same car
        if ($car['id'] == $selectedCar['id']) continue;

        $score = 0;

        // Matching features
        if ($car['type'] === $selectedCar['type']) $score += 2;
        if ($car['brand'] === $selectedCar['brand']) $score += 2;
        if ($car['fuel'] === $selectedCar['fuel']) $score += 1;

        // Close in mileage
        if (abs($car['mileage'] - $selectedCar['mileage']) < 5) $score += 1;

        // Close in cost per day
        if (abs($car['cost_per_day'] - $selectedCar['cost_per_day']) < 200) $score += 1;

        if ($score > 0) {
            $recommendations[] = ['car' => $car, 'score' => $score];
        }
    }

    // Sort by score descending
    usort($recommendations, function ($a, $b) {
        return $b['score'] - $a['score'];
    });

    return array_slice($recommendations, 0, 3); // top 3
}

// ---------------------------------------------
// Greedy Algorithm for Car Allocation & Scheduling
// ---------------------------------------------
function allocateCarGreedy($requestedStart, $requestedEnd, $cars, $bookings) {
    $bestCar = null;
    $minIdleTime = PHP_INT_MAX;

    foreach ($cars as $car) {
        $available = true;
        $latestBookingEnd = null;

        foreach ($bookings as $booking) {
            if ($booking['car_id'] == $car['id']) {
                // Check date overlap (not available)
                if (!($requestedEnd < $booking['start_date'] || $requestedStart > $booking['end_date'])) {
                    $available = false;
                    break;
                }

                // Track latest past booking
                if ($booking['end_date'] < $requestedStart) {
                    $latestBookingEnd = max($latestBookingEnd ?? 0, strtotime($booking['end_date']));
                }
            }
        }

        if ($available) {
            $idleTime = ($latestBookingEnd) ? strtotime($requestedStart) - $latestBookingEnd : 0;
            if ($idleTime < $minIdleTime) {
                $minIdleTime = $idleTime;
                $bestCar = $car;
            }
        }
    }

    return $bestCar; // null if no car found
}
?>
