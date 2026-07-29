<?php
// Tool (function-calling) definitions and executors for the chatbot.
// Every tool that touches bookings/account data enforces login server-side —
// the model's own judgement is never trusted for access control.

function chatbotToolDefinitions()
{
    return [
        [
            'type' => 'function',
            'function' => [
                'name' => 'check_login_status',
                'description' => 'Check whether the current visitor is logged in, and get their name/email if so. Always call this before attempting to book a car or list bookings.',
                'parameters' => ['type' => 'object', 'properties' => new stdClass(), 'required' => []],
            ],
        ],
        [
            'type' => 'function',
            'function' => [
                'name' => 'search_cars',
                'description' => 'Search available rental cars by free-text keyword (matches car title, brand, or fuel type). Leave keyword empty to list a few recent cars. Returns each car\'s id, title, brand, price per day (NRS), fuel type, seats, and model year.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'keyword' => ['type' => 'string', 'description' => 'Free-text search term, e.g. a brand, model, or fuel type'],
                    ],
                    'required' => [],
                ],
            ],
        ],
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_car_details',
                'description' => 'Get full details for one car by its id, including overview description.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'vehicle_id' => ['type' => 'integer', 'description' => 'The car id, from search_cars results'],
                    ],
                    'required' => ['vehicle_id'],
                ],
            ],
        ],
        [
            'type' => 'function',
            'function' => [
                'name' => 'create_booking',
                'description' => 'Book a car for the logged-in user for a date range. Only call this after check_login_status confirms the user is logged in, and after confirming the car and dates with the user. Dates must be YYYY-MM-DD, to_date after from_date, from_date not in the past.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'vehicle_id' => ['type' => 'integer', 'description' => 'The id of the car to book'],
                        'from_date' => ['type' => 'string', 'description' => 'Start date, format YYYY-MM-DD'],
                        'to_date' => ['type' => 'string', 'description' => 'End date, format YYYY-MM-DD'],
                        'message' => ['type' => 'string', 'description' => 'Optional note from the user for this booking'],
                    ],
                    'required' => ['vehicle_id', 'from_date', 'to_date'],
                ],
            ],
        ],
        [
            'type' => 'function',
            'function' => [
                'name' => 'list_my_bookings',
                'description' => "List the logged-in user's bookings with status (Confirmed / Cancelled / Not confirmed yet), dates, car info, and total price.",
                'parameters' => ['type' => 'object', 'properties' => new stdClass(), 'required' => []],
            ],
        ],
    ];
}

function chatbotExecuteTool($name, $args, PDO $dbh)
{
    switch ($name) {
        case 'check_login_status':
            return chatbotCheckLogin($dbh);

        case 'search_cars':
            $keyword = isset($args['keyword']) ? trim((string) $args['keyword']) : '';
            return chatbotSearchCars($dbh, $keyword);

        case 'get_car_details':
            $vhid = isset($args['vehicle_id']) ? intval($args['vehicle_id']) : 0;
            return chatbotGetCarDetails($dbh, $vhid);

        case 'create_booking':
            return chatbotCreateBooking($dbh, $args);

        case 'list_my_bookings':
            return chatbotListBookings($dbh);

        default:
            return ['error' => 'unknown_tool', 'message' => 'Unknown tool: ' . $name];
    }
}

function chatbotCheckLogin(PDO $dbh)
{
    if (empty($_SESSION['login'])) {
        return ['logged_in' => false];
    }
    $email = $_SESSION['login'];
    $q = $dbh->prepare("SELECT FullName FROM tblusers WHERE EmailId = :email");
    $q->bindParam(':email', $email, PDO::PARAM_STR);
    $q->execute();
    $name = $q->fetchColumn();
    return ['logged_in' => true, 'email' => $email, 'name' => $name ?: null];
}

function chatbotSearchCars(PDO $dbh, $keyword)
{
    if ($keyword !== '') {
        $sql = "SELECT tblvehicles.id, tblvehicles.VehiclesTitle, tblbrands.BrandName, tblvehicles.PricePerDay,
                       tblvehicles.FuelType, tblvehicles.SeatingCapacity, tblvehicles.ModelYear
                FROM tblvehicles JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
                WHERE tblvehicles.VehiclesTitle LIKE :kw OR tblbrands.BrandName LIKE :kw
                   OR tblvehicles.FuelType LIKE :kw OR tblvehicles.ModelYear LIKE :kw
                LIMIT 8";
        $q = $dbh->prepare($sql);
        $like = '%' . $keyword . '%';
        $q->bindParam(':kw', $like, PDO::PARAM_STR);
    } else {
        $sql = "SELECT tblvehicles.id, tblvehicles.VehiclesTitle, tblbrands.BrandName, tblvehicles.PricePerDay,
                       tblvehicles.FuelType, tblvehicles.SeatingCapacity, tblvehicles.ModelYear
                FROM tblvehicles JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
                ORDER BY tblvehicles.id DESC LIMIT 8";
        $q = $dbh->prepare($sql);
    }
    $q->execute();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        return ['results' => [], 'note' => 'No cars matched that search.'];
    }

    $results = [];
    foreach ($rows as $r) {
        $results[] = [
            'id' => (int) $r['id'],
            'title' => $r['VehiclesTitle'],
            'brand' => $r['BrandName'],
            'price_per_day' => (float) $r['PricePerDay'],
            'fuel_type' => $r['FuelType'],
            'seats' => (int) $r['SeatingCapacity'],
            'model_year' => $r['ModelYear'],
        ];
    }
    return ['results' => $results];
}

function chatbotGetCarDetails(PDO $dbh, $vhid)
{
    if (!$vhid) {
        return ['error' => 'invalid_input', 'message' => 'vehicle_id is required.'];
    }
    $q = $dbh->prepare("SELECT tblvehicles.*, tblbrands.BrandName FROM tblvehicles
                         JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
                         WHERE tblvehicles.id = :id");
    $q->bindParam(':id', $vhid, PDO::PARAM_INT);
    $q->execute();
    $row = $q->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return ['error' => 'not_found', 'message' => 'No car found with that id.'];
    }

    return [
        'id' => (int) $row['id'],
        'title' => $row['VehiclesTitle'],
        'brand' => $row['BrandName'],
        'price_per_day' => (float) $row['PricePerDay'],
        'fuel_type' => $row['FuelType'],
        'seats' => (int) $row['SeatingCapacity'],
        'model_year' => $row['ModelYear'],
        'overview' => $row['VehiclesOverview'],
    ];
}

function chatbotCreateBooking(PDO $dbh, $args)
{
    if (empty($_SESSION['login'])) {
        return ['error' => 'not_logged_in', 'message' => 'The user is not logged in. Ask them to log in or register first, then try again.'];
    }

    $vhid = isset($args['vehicle_id']) ? intval($args['vehicle_id']) : 0;
    $fromdate = isset($args['from_date']) ? trim((string) $args['from_date']) : '';
    $todate = isset($args['to_date']) ? trim((string) $args['to_date']) : '';
    $message = isset($args['message']) ? trim((string) $args['message']) : '';

    if (!$vhid || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromdate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $todate)) {
        return ['error' => 'invalid_input', 'message' => 'vehicle_id, from_date and to_date (YYYY-MM-DD) are all required and dates must be valid.'];
    }
    if (strtotime($todate) <= strtotime($fromdate)) {
        return ['error' => 'invalid_dates', 'message' => 'to_date must be after from_date.'];
    }
    if (strtotime($fromdate) < strtotime(date('Y-m-d'))) {
        return ['error' => 'invalid_dates', 'message' => 'from_date cannot be in the past.'];
    }

    $vq = $dbh->prepare("SELECT tblvehicles.id, tblvehicles.VehiclesTitle, tblbrands.BrandName, tblvehicles.PricePerDay
                          FROM tblvehicles JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
                          WHERE tblvehicles.id = :id");
    $vq->bindParam(':id', $vhid, PDO::PARAM_INT);
    $vq->execute();
    $vehicle = $vq->fetch(PDO::FETCH_ASSOC);
    if (!$vehicle) {
        return ['error' => 'not_found', 'message' => 'No car found with that id. Search again to get a valid id.'];
    }

    $useremail = $_SESSION['login'];

    // Same overlap-check pattern used by the existing booking form on vehical-details.php.
    $overlapSql = "SELECT * FROM tblbooking
                    WHERE (:fromdate BETWEEN date(FromDate) and date(ToDate)
                        || :todate BETWEEN date(FromDate) and date(ToDate)
                        || date(FromDate) BETWEEN :fromdate and :todate)
                      AND VehicleId = :vhid";
    $oq = $dbh->prepare($overlapSql);
    $oq->bindParam(':vhid', $vhid, PDO::PARAM_INT);
    $oq->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
    $oq->bindParam(':todate', $todate, PDO::PARAM_STR);
    $oq->execute();
    if ($oq->rowCount() > 0) {
        return ['error' => 'unavailable', 'message' => 'This car is already booked for an overlapping date range. Suggest different dates or another car.'];
    }

    $bookingno = mt_rand(100000000, 999999999);
    $status = 0;
    $ins = $dbh->prepare("INSERT INTO tblbooking(BookingNumber,userEmail,VehicleId,FromDate,ToDate,message,Status)
                           VALUES(:bookingno,:useremail,:vhid,:fromdate,:todate,:message,:status)");
    $ins->bindParam(':bookingno', $bookingno, PDO::PARAM_STR);
    $ins->bindParam(':useremail', $useremail, PDO::PARAM_STR);
    $ins->bindParam(':vhid', $vhid, PDO::PARAM_INT);
    $ins->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
    $ins->bindParam(':todate', $todate, PDO::PARAM_STR);
    $ins->bindParam(':message', $message, PDO::PARAM_STR);
    $ins->bindParam(':status', $status, PDO::PARAM_INT);
    $ins->execute();

    if (!$dbh->lastInsertId()) {
        return ['error' => 'insert_failed', 'message' => 'Booking could not be created. Ask the user to try again.'];
    }

    return [
        'success' => true,
        'booking_number' => (string) $bookingno,
        'car' => $vehicle['BrandName'] . ' ' . $vehicle['VehiclesTitle'],
        'from_date' => $fromdate,
        'to_date' => $todate,
        'status' => 'Not confirmed yet',
    ];
}

function chatbotListBookings(PDO $dbh)
{
    if (empty($_SESSION['login'])) {
        return ['error' => 'not_logged_in', 'message' => 'The user is not logged in. Ask them to log in first.'];
    }

    $useremail = $_SESSION['login'];
    $sql = "SELECT tblvehicles.VehiclesTitle, tblbrands.BrandName, tblbooking.FromDate, tblbooking.ToDate,
                   tblbooking.Status, tblbooking.BookingNumber, tblvehicles.PricePerDay,
                   DATEDIFF(tblbooking.ToDate, tblbooking.FromDate) as TotalDays
            FROM tblbooking
            JOIN tblvehicles ON tblbooking.VehicleId = tblvehicles.id
            JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
            WHERE tblbooking.userEmail = :useremail
            ORDER BY tblbooking.id DESC LIMIT 10";
    $q = $dbh->prepare($sql);
    $q->bindParam(':useremail', $useremail, PDO::PARAM_STR);
    $q->execute();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        return ['bookings' => [], 'note' => 'This user has no bookings yet.'];
    }

    $statusMap = [0 => 'Not confirmed yet', 1 => 'Confirmed', 2 => 'Cancelled'];
    $bookings = [];
    foreach ($rows as $r) {
        $days = max(1, (int) $r['TotalDays']);
        $bookings[] = [
            'booking_number' => (string) $r['BookingNumber'],
            'car' => $r['BrandName'] . ' ' . $r['VehiclesTitle'],
            'from_date' => $r['FromDate'],
            'to_date' => $r['ToDate'],
            'status' => $statusMap[(int) $r['Status']] ?? 'Unknown',
            'total_days' => $days,
            'price_per_day' => (float) $r['PricePerDay'],
            'total_amount' => $days * (float) $r['PricePerDay'],
        ];
    }
    return ['bookings' => $bookings];
}
