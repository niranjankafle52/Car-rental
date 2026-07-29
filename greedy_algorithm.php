<?php
/**
 * Greedy Algorithm for Car Rental Optimization
 * 
 * This algorithm optimizes car selection based on multiple criteria:
 * 1. Price per day (lower is better)
 * 2. Availability (higher availability score is better)
 * 3. User preferences (fuel type, seating capacity, brand)
 * 4. Vehicle condition and features
 */

class CarRentalGreedyAlgorithm {
    private $dbh;
    
    public function __construct($database) {
        $this->dbh = $database;
    }
    
    /**
     * Main greedy algorithm function
     * @param array $criteria - User preferences and constraints
     * @param string $fromDate - Pickup date
     * @param string $toDate - Return date
     * @return array - Optimized car recommendations
     */
    public function findOptimalCars($criteria, $fromDate, $toDate) {
        // Get all available cars for the given date range
        $availableCars = $this->getAvailableCars($fromDate, $toDate);
        
        if (empty($availableCars)) {
            return [];
        }
        
        // Calculate scores for each car using greedy approach
        $scoredCars = $this->calculateCarScores($availableCars, $criteria);
        
        // Sort cars by score (greedy choice - always pick the best available option)
        usort($scoredCars, function($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });
        
        // Return top recommendations (greedy selection)
        return array_slice($scoredCars, 0, 10); // Top 10 recommendations
    }
    
    /**
     * Get available cars for given date range
     */
    private function getAvailableCars($fromDate, $toDate) {
        $sql = "SELECT tblvehicles.*, tblbrands.BrandName 
                FROM tblvehicles 
                JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand 
                WHERE tblvehicles.id NOT IN (
                    SELECT VehicleId FROM tblbooking 
                    WHERE Status IN (0, 1) 
                    AND (
                        (? BETWEEN FromDate AND ToDate) OR 
                        (? BETWEEN FromDate AND ToDate) OR
                        (FromDate BETWEEN ? AND ?) OR
                        (ToDate BETWEEN ? AND ?)
                    )
                )";
        
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calculate scores for each car using greedy approach
     * Higher score = better choice
     */
    private function calculateCarScores($cars, $criteria) {
        $scoredCars = [];
        
        foreach ($cars as $car) {
            $score = 0;
            $scoreBreakdown = [];
            
            // 1. Price Score (40% weight) - Lower price = higher score
            $priceScore = $this->calculatePriceScore($car['PricePerDay'], $criteria['max_price'] ?? 10000);
            $score += $priceScore * 0.4;
            $scoreBreakdown['price_score'] = $priceScore;
            
            // 2. Availability Score (20% weight) - Based on how often car is booked
            $availabilityScore = $this->calculateAvailabilityScore($car['id']);
            $score += $availabilityScore * 0.2;
            $scoreBreakdown['availability_score'] = $availabilityScore;
            
            // 3. Preference Score (25% weight) - Matches user preferences
            $preferenceScore = $this->calculatePreferenceScore($car, $criteria);
            $score += $preferenceScore * 0.25;
            $scoreBreakdown['preference_score'] = $preferenceScore;
            
            // 4. Quality Score (15% weight) - Based on vehicle features
            $qualityScore = $this->calculateQualityScore($car);
            $score += $qualityScore * 0.15;
            $scoreBreakdown['quality_score'] = $qualityScore;
            
            $car['total_score'] = round($score, 2);
            $car['score_breakdown'] = $scoreBreakdown;
            $scoredCars[] = $car;
        }
        
        return $scoredCars;
    }
    
    /**
     * Calculate price score (0-100)
     * Lower price gets higher score
     */
    private function calculatePriceScore($pricePerDay, $maxPrice) {
        if ($pricePerDay <= 0) return 0;
        
        // Normalize price score (inverse relationship)
        $normalizedPrice = ($maxPrice - $pricePerDay) / $maxPrice;
        return max(0, min(100, $normalizedPrice * 100));
    }
    
    /**
     * Calculate availability score (0-100)
     * Based on booking frequency - less booked = higher score
     */
    private function calculateAvailabilityScore($vehicleId) {
        $sql = "SELECT COUNT(*) as booking_count 
                FROM tblbooking 
                WHERE VehicleId = ? AND Status IN (0, 1)";
        
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$vehicleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $bookingCount = $result['booking_count'];
        
        // Less bookings = higher availability score
        if ($bookingCount == 0) return 100;
        if ($bookingCount <= 5) return 80;
        if ($bookingCount <= 10) return 60;
        if ($bookingCount <= 20) return 40;
        return 20;
    }
    
    /**
     * Calculate preference score (0-100)
     * Based on how well car matches user preferences
     */
    private function calculatePreferenceScore($car, $criteria) {
        $score = 0;
        $maxScore = 0;
        
        // Fuel type preference (30 points)
        if (isset($criteria['fuel_type']) && !empty($criteria['fuel_type'])) {
            $maxScore += 30;
            if (strtolower($car['FuelType']) === strtolower($criteria['fuel_type'])) {
                $score += 30;
            }
        }
        
        // Seating capacity preference (25 points)
        if (isset($criteria['seating_capacity']) && !empty($criteria['seating_capacity'])) {
            $maxScore += 25;
            $preferredSeats = (int)$criteria['seating_capacity'];
            $carSeats = (int)$car['SeatingCapacity'];
            
            if ($carSeats == $preferredSeats) {
                $score += 25;
            } elseif (abs($carSeats - $preferredSeats) == 1) {
                $score += 15; // Close match
            } elseif (abs($carSeats - $preferredSeats) == 2) {
                $score += 10; // Reasonable match
            }
        }
        
        // Brand preference (25 points)
        if (isset($criteria['brand']) && !empty($criteria['brand'])) {
            $maxScore += 25;
            if (strtolower($car['BrandName']) === strtolower($criteria['brand'])) {
                $score += 25;
            }
        }
        
        // Model year preference (20 points)
        if (isset($criteria['min_year']) && !empty($criteria['min_year'])) {
            $maxScore += 20;
            $carYear = (int)$car['ModelYear'];
            $minYear = (int)$criteria['min_year'];
            
            if ($carYear >= $minYear) {
                $score += 20;
            } elseif ($carYear >= ($minYear - 2)) {
                $score += 10; // Close to preferred year
            }
        }
        
        // Return percentage score
        return $maxScore > 0 ? ($score / $maxScore) * 100 : 50; // Default 50 if no preferences
    }
    
    /**
     * Calculate quality score (0-100)
     * Based on vehicle features and condition
     */
    private function calculateQualityScore($car) {
        $score = 0;
        
        // Newer cars get higher score
        $currentYear = date('Y');
        $carYear = (int)$car['ModelYear'];
        $ageScore = max(0, 50 - (($currentYear - $carYear) * 5));
        $score += $ageScore;
        
        // More seats generally indicate better family car (up to a point)
        $seatingScore = min(30, (int)$car['SeatingCapacity'] * 5);
        $score += $seatingScore;
        
        // Fuel type preference (Diesel generally more efficient)
        if (strtolower($car['FuelType']) === 'diesel') {
            $score += 20;
        } else {
            $score += 15;
        }
        
        return min(100, $score);
    }
    
    /**
     * Get algorithm explanation for transparency
     */
    public function getAlgorithmExplanation() {
        return [
            'algorithm_name' => 'Greedy Car Rental Optimization',
            'description' => 'This algorithm uses a greedy approach to find the best car matches by considering multiple criteria.',
            'criteria_weights' => [
                'price' => '40% - Lower price per day gets higher score',
                'availability' => '20% - Less frequently booked cars get higher score',
                'preferences' => '25% - Better match with user preferences gets higher score',
                'quality' => '15% - Newer cars with better features get higher score'
            ],
            'greedy_choice' => 'At each step, the algorithm selects the car with the highest total score, making locally optimal choices that lead to globally optimal results.'
        ];
    }
}
?>
