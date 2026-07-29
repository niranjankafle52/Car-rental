<?php
// ---------------------------------------------
// Enhanced Greedy Algorithm for Vehicle Selection
// ---------------------------------------------

class VehicleSelectionAlgorithm {
    
    // Vehicle type categories
    const VEHICLE_TYPES = [
        'SUV' => ['weight' => 3, 'seats_range' => [5, 7, 8]],
        'Sedan' => ['weight' => 2, 'seats_range' => [4, 5]],
        'Hatchback' => ['weight' => 1, 'seats_range' => [4, 5]],
        'Sports' => ['weight' => 4, 'seats_range' => [2, 4]],
        'Luxury' => ['weight' => 5, 'seats_range' => [4, 5, 6]],
        'Van' => ['weight' => 2, 'seats_range' => [7, 8, 9]],
        'Truck' => ['weight' => 1, 'seats_range' => [2, 3, 4]]
    ];
    
    // Fuel type preferences
    const FUEL_PREFERENCES = [
        'Petrol' => 1,
        'Diesel' => 1.2,
        'Electric' => 1.5,
        'Hybrid' => 1.3,
        'CNG' => 0.8
    ];
    
    // Brand preferences (can be customized)
    const BRAND_PREFERENCES = [
        'BMW' => 1.4,
        'Audi' => 1.3,
        'Mercedes' => 1.5,
        'Toyota' => 1.1,
        'Honda' => 1.0,
        'Maruti' => 0.9,
        'Hyundai' => 1.0,
        'Nissan' => 0.9
    ];
    
    /**
     * Enhanced Greedy Algorithm for Vehicle Selection
     * @param array $userPreferences User preferences (seats, budget, vehicle_type, etc.)
     * @param array $availableVehicles Available vehicles from database
     * @param array $bookings Existing bookings
     * @return array Best vehicle matches with scores
     */
    public function selectVehicleGreedy($userPreferences, $availableVehicles, $bookings = []) {
        $scoredVehicles = [];
        
        foreach ($availableVehicles as $vehicle) {
            // Check availability first
            if (!$this->isVehicleAvailable($vehicle['id'], $userPreferences['start_date'], $userPreferences['end_date'], $bookings)) {
                continue;
            }
            
            $score = $this->calculateVehicleScore($vehicle, $userPreferences);
            
            if ($score > 0) {
                $scoredVehicles[] = [
                    'vehicle' => $vehicle,
                    'score' => $score,
                    'match_details' => $this->getMatchDetails($vehicle, $userPreferences)
                ];
            }
        }
        
        // Sort by score descending (greedy approach - best score first)
        usort($scoredVehicles, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $scoredVehicles;
    }
    
    /**
     * Calculate comprehensive score for a vehicle
     */
    private function calculateVehicleScore($vehicle, $userPreferences) {
        $score = 0;
        
        // 1. Seating Capacity Score (40% weight)
        $seatsScore = $this->calculateSeatsScore($vehicle['SeatingCapacity'], $userPreferences['required_seats']);
        $score += $seatsScore * 0.4;
        
        // 2. Vehicle Type Score (25% weight)
        $typeScore = $this->calculateTypeScore($vehicle, $userPreferences);
        $score += $typeScore * 0.25;
        
        // 3. Budget Score (20% weight)
        $budgetScore = $this->calculateBudgetScore($vehicle['PricePerDay'], $userPreferences['max_budget']);
        $score += $budgetScore * 0.2;
        
        // 4. Brand Preference Score (10% weight)
        $brandScore = $this->calculateBrandScore($vehicle['BrandName'], $userPreferences);
        $score += $brandScore * 0.1;
        
        // 5. Fuel Type Score (5% weight)
        $fuelScore = $this->calculateFuelScore($vehicle['FuelType'], $userPreferences);
        $score += $fuelScore * 0.05;
        
        return $score;
    }
    
    /**
     * Calculate seats compatibility score
     */
    private function calculateSeatsScore($vehicleSeats, $requiredSeats) {
        if ($vehicleSeats >= $requiredSeats) {
            // Perfect match or better
            if ($vehicleSeats == $requiredSeats) {
                return 10; // Perfect match
            } elseif ($vehicleSeats <= $requiredSeats + 2) {
                return 8; // Good match (slight excess)
            } else {
                return 6; // Acceptable but oversized
            }
        } else {
            return 0; // Insufficient seats
        }
    }
    
    /**
     * Calculate vehicle type score
     */
    private function calculateTypeScore($vehicle, $userPreferences) {
        $score = 0;
        
        // Determine vehicle type from title and brand
        $vehicleType = $this->determineVehicleType($vehicle['VehiclesTitle'], $vehicle['BrandName']);
        
        if (isset($userPreferences['preferred_type']) && $userPreferences['preferred_type'] == $vehicleType) {
            $score += 10; // Exact type match
        } elseif (isset($userPreferences['preferred_type'])) {
            // Check if type is in acceptable range
            $acceptableTypes = $userPreferences['acceptable_types'] ?? [];
            if (in_array($vehicleType, $acceptableTypes)) {
                $score += 6;
            }
        } else {
            // No preference, give base score
            $score += 5;
        }
        
        // Add type weight bonus
        if (isset(self::VEHICLE_TYPES[$vehicleType])) {
            $score += self::VEHICLE_TYPES[$vehicleType]['weight'];
        }
        
        return $score;
    }
    
    /**
     * Calculate budget compatibility score
     */
    private function calculateBudgetScore($vehiclePrice, $maxBudget) {
        if ($vehiclePrice <= $maxBudget) {
            // Within budget
            $percentage = ($vehiclePrice / $maxBudget) * 100;
            if ($percentage <= 70) {
                return 10; // Great value
            } elseif ($percentage <= 85) {
                return 8; // Good value
            } else {
                return 6; // Acceptable
            }
        } else {
            return 0; // Over budget
        }
    }
    
    /**
     * Calculate brand preference score
     */
    private function calculateBrandScore($brandName, $userPreferences) {
        $score = 5; // Base score
        
        // Check user brand preferences
        if (isset($userPreferences['preferred_brands']) && in_array($brandName, $userPreferences['preferred_brands'])) {
            $score += 5;
        }
        
        // Add brand weight
        if (isset(self::BRAND_PREFERENCES[$brandName])) {
            $score *= self::BRAND_PREFERENCES[$brandName];
        }
        
        return $score;
    }
    
    /**
     * Calculate fuel type score
     */
    private function calculateFuelScore($fuelType, $userPreferences) {
        $score = 5; // Base score
        
        // Check user fuel preferences
        if (isset($userPreferences['preferred_fuel']) && $userPreferences['preferred_fuel'] == $fuelType) {
            $score += 5;
        }
        
        // Add fuel type weight
        if (isset(self::FUEL_PREFERENCES[$fuelType])) {
            $score *= self::FUEL_PREFERENCES[$fuelType];
        }
        
        return $score;
    }
    
    /**
     * Determine vehicle type from title and brand
     */
    private function determineVehicleType($title, $brand) {
        $title = strtolower($title);
        $brand = strtolower($brand);
        
        if (strpos($title, 'suv') !== false || strpos($title, 'crossover') !== false) {
            return 'SUV';
        } elseif (strpos($title, 'sedan') !== false) {
            return 'Sedan';
        } elseif (strpos($title, 'hatchback') !== false || strpos($title, 'wagon') !== false) {
            return 'Hatchback';
        } elseif (strpos($title, 'sports') !== false || strpos($title, 'gt') !== false || strpos($title, 'coupe') !== false) {
            return 'Sports';
        } elseif (strpos($brand, 'bmw') !== false || strpos($brand, 'audi') !== false || strpos($brand, 'mercedes') !== false) {
            return 'Luxury';
        } elseif (strpos($title, 'van') !== false) {
            return 'Van';
        } elseif (strpos($title, 'truck') !== false || strpos($title, 'pickup') !== false) {
            return 'Truck';
        } else {
            return 'Sedan'; // Default
        }
    }
    
    /**
     * Check if vehicle is available for given dates
     */
    private function isVehicleAvailable($vehicleId, $startDate, $endDate, $bookings) {
        foreach ($bookings as $booking) {
            if ($booking['VehicleId'] == $vehicleId) {
                // Check for date overlap
                if (!($endDate < $booking['FromDate'] || $startDate > $booking['ToDate'])) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Get detailed match information
     */
    private function getMatchDetails($vehicle, $userPreferences) {
        $details = [];
        
        // Seats match
        if ($vehicle['SeatingCapacity'] >= $userPreferences['required_seats']) {
            $details['seats'] = '✓ Perfect match';
        } else {
            $details['seats'] = '✗ Insufficient seats';
        }
        
        // Budget match
        if ($vehicle['PricePerDay'] <= $userPreferences['max_budget']) {
            $details['budget'] = '✓ Within budget';
        } else {
            $details['budget'] = '✗ Over budget';
        }
        
        // Type match
        $vehicleType = $this->determineVehicleType($vehicle['VehiclesTitle'], $vehicle['BrandName']);
        if (isset($userPreferences['preferred_type']) && $userPreferences['preferred_type'] == $vehicleType) {
            $details['type'] = '✓ Preferred type';
        } else {
            $details['type'] = 'Available type: ' . $vehicleType;
        }
        
        return $details;
    }
    
    /**
     * Get top recommendations with explanations
     */
    public function getTopRecommendations($userPreferences, $availableVehicles, $bookings = [], $limit = 5) {
        $scoredVehicles = $this->selectVehicleGreedy($userPreferences, $availableVehicles, $bookings);
        
        $recommendations = [];
        foreach (array_slice($scoredVehicles, 0, $limit) as $index => $item) {
            $recommendations[] = [
                'rank' => $index + 1,
                'vehicle' => $item['vehicle'],
                'score' => round($item['score'], 2),
                'match_details' => $item['match_details'],
                'explanation' => $this->generateExplanation($item['vehicle'], $userPreferences, $item['score'])
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Generate explanation for recommendation
     */
    private function generateExplanation($vehicle, $userPreferences, $score) {
        $explanations = [];
        
        if ($vehicle['SeatingCapacity'] >= $userPreferences['required_seats']) {
            $explanations[] = "Perfect seating capacity ({$vehicle['SeatingCapacity']} seats)";
        }
        
        if ($vehicle['PricePerDay'] <= $userPreferences['max_budget']) {
            $explanations[] = "Within your budget (NRS {$vehicle['PricePerDay']}/day)";
        }
        
        $vehicleType = $this->determineVehicleType($vehicle['VehiclesTitle'], $vehicle['BrandName']);
        if (isset($userPreferences['preferred_type']) && $userPreferences['preferred_type'] == $vehicleType) {
            $explanations[] = "Matches your preferred vehicle type ({$vehicleType})";
        }
        
        if (count($explanations) == 0) {
            $explanations[] = "Good overall match based on availability and features";
        }
        
        return implode(', ', $explanations);
    }
}

// ---------------------------------------------
// Usage Example
// ---------------------------------------------

/*
// Example usage:
$algorithm = new VehicleSelectionAlgorithm();

$userPreferences = [
    'required_seats' => 5,
    'max_budget' => 1000,
    'preferred_type' => 'SUV',
    'acceptable_types' => ['SUV', 'Sedan'],
    'preferred_brands' => ['Toyota', 'Honda'],
    'preferred_fuel' => 'Petrol',
    'start_date' => '2024-01-15',
    'end_date' => '2024-01-20'
];

$recommendations = $algorithm->getTopRecommendations($userPreferences, $availableVehicles, $bookings);

foreach ($recommendations as $rec) {
    echo "Rank {$rec['rank']}: {$rec['vehicle']['VehiclesTitle']} (Score: {$rec['score']})\n";
    echo "Explanation: {$rec['explanation']}\n";
    echo "---\n";
}
*/
?> 