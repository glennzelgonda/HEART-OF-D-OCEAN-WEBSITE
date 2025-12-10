<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Function to normalize cottage names (Matches availability.php) - FIXED VERSION
function normalizeCottageName($name) {
    $name = trim($name);
    
    // Remove price from cottage name (if present)
    if (strpos($name, '—') !== false) {
        $parts = explode('—', $name);
        $name = trim($parts[0]);
    }
    
    // Also try dash with price
    if (strpos($name, '- ₱') !== false) {
        $parts = explode('- ₱', $name);
        $name = trim($parts[0]);
    }
    
    // Remove any currency symbols and numbers at the end
    $name = preg_replace('/\s*[—-]\s*₱\s*\d+[,\d\.]*/', '', $name);
    $name = preg_replace('/- ₱\d+[,\d\.]*/', '', $name);
    
    // Map slug names to display names WITHOUT PRICE (matches availability.php)
    $slugMap = [
        'stephs-848' => "Steph's 848",
        'stephs-846' => "Steph's 846",
        'premium-838' => 'Premium 838',
        'premium-840' => 'Premium 840',
        'beatrice-a' => 'Beatrice A',
        'beatrice-b' => 'Beatrice B',
        'concierge-817' => 'Concierge 817',
        'concierge-815-819' => 'Concierge 815/819',
        'de-luxe' => 'De Luxe',
        'aqua-class' => 'Aqua Class',
        'heartsuite' => 'Heartsuite',
        'penthouse' => 'Penthouse',
        'white-house' => 'White House',
        'giant-kubo' => 'Giant Kubo',
        'seaside-whole' => 'Seaside (Whole)',
        'seaside-half' => 'Seaside (Half)',
        'bamboo-kubo' => 'Bamboo Kubo',
        'stephs-skylounge-842-844' => "Steph's Skylounge 842/844",
        'stephs-skylounge' => "Steph's Skylounge 842/844"
    ];
    
    $lowerName = strtolower($name);
    foreach ($slugMap as $slug => $display) {
        if ($lowerName === $slug) {
            return $display;
        }
    }
    
    // Try partial match
    foreach ($slugMap as $slug => $display) {
        if (strpos($lowerName, $slug) !== false) {
            return $display;
        }
    }
    
    return trim($name);
}

try {
    // Get booked cottages for next 7 days - ONLY FROM NON-DELETED BOOKINGS
    $bookedQuery = "SELECT 
        ca.cottage_name,
        ca.booked_date,
        ca.status,
        b.name,
        b.booking_id
        FROM cottage_availability ca
        LEFT JOIN bookings b ON ca.booking_id = b.booking_id
        WHERE ca.booked_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND ca.status = 'confirmed'
        AND (b.deleted = 0 OR b.deleted IS NULL)
        ORDER BY ca.booked_date, ca.cottage_name";
    
    $bookedStmt = $db->prepare($bookedQuery);
    $bookedStmt->execute();
    $bookedData = $bookedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Normalize cottage names in booked data - IMPORTANT: Use same function as availability.php
    foreach ($bookedData as &$booking) {
        $booking['cottage_name'] = normalizeCottageName($booking['cottage_name']);
    }

    // Get all cottage display names (for reference) - WITHOUT PRICES
    $allCottageNames = [
        'White House',
        'Penthouse',
        'Aqua Class',
        'Heartsuite',
        "Steph's Skylounge 842/844",
        "Steph's 848",
        "Steph's 846",
        'Concierge 817',
        'De Luxe',
        'Concierge 815/819',
        'Premium 840',
        'Beatrice A',
        'Premium 838',
        'Beatrice B',
        'Giant Kubo',
        'Seaside (Whole)',
        'Seaside (Half)',
        'Bamboo Kubo'
    ];

    // Generate next 7 days
    $dates = [];
    for ($i = 0; $i < 7; $i++) {
        $dates[] = date('Y-m-d', strtotime("+$i days"));
    }

    // Create availability matrix data structure
    $availabilityMatrix = [];
    
    // First, create a lookup of booked dates by cottage
    $bookedLookup = [];
    foreach ($bookedData as $booking) {
        $cottage = $booking['cottage_name'];
        $date = $booking['booked_date'];
        if (!isset($bookedLookup[$cottage])) {
            $bookedLookup[$cottage] = [];
        }
        $bookedLookup[$cottage][$date] = true;
    }
    
    // Build matrix for each cottage
    foreach ($allCottageNames as $cottage) {
        $row = ['cottage_name' => $cottage];
        
        foreach ($dates as $date) {
            $row[$date] = isset($bookedLookup[$cottage][$date]) ? 'booked' : 'available';
        }
        
        $availabilityMatrix[] = $row;
    }

    // Get available cottages (for backward compatibility)
    $availableData = [];
    foreach ($dates as $date) {
        foreach ($allCottageNames as $cottage) {
            if (!isset($bookedLookup[$cottage][$date])) {
                $availableData[] = [
                    'cottage_name' => $cottage,
                    'booked_date' => $date,
                    'status' => 'available'
                ];
            }
        }
    }

    // Calculate summary
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    $todayBooked = array_filter($bookedData, function($item) use ($today) {
        return $item['booked_date'] == $today;
    });
    
    $tomorrowBooked = array_filter($bookedData, function($item) use ($tomorrow) {
        return $item['booked_date'] == $tomorrow;
    });
    
    $todayAvailable = array_filter($availableData, function($item) use ($today) {
        return $item['booked_date'] == $today;
    });
    
    $tomorrowAvailable = array_filter($availableData, function($item) use ($tomorrow) {
        return $item['booked_date'] == $tomorrow;
    });

    $summary = [
        'booked_dates' => count($bookedData),
        'available_dates' => count($availableData),
        'total_dates' => count($bookedData) + count($availableData),
        'today_booked' => count($todayBooked),
        'today_available' => count($todayAvailable),
        'tomorrow_booked' => count($tomorrowBooked),
        'tomorrow_available' => count($tomorrowAvailable)
    ];

    echo json_encode([
        'booked' => $bookedData,
        'available' => $availableData,
        'matrix' => $availabilityMatrix,
        'summary' => $summary,
        'dates' => $dates,
        'cottages' => $allCottageNames
    ]);

} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>