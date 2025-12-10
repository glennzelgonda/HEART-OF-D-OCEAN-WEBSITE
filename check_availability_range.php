<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkin = $_POST['checkin_date'] ?? '';
    $checkout = $_POST['checkout_date'] ?? '';

    // Validations
    if (!$checkin || !$checkout) {
        echo json_encode(['booked_cottages' => []]);
        exit;
    }

    try {
        $query = "SELECT DISTINCT cottage_name FROM cottage_availability 
                  WHERE status = 'confirmed' 
                  AND booked_date >= :checkin 
                  AND booked_date < :checkout";
                  
        $stmt = $db->prepare($query);
        $stmt->bindParam(":checkin", $checkin);
        $stmt->bindParam(":checkout", $checkout);
        $stmt->execute();
        
        $db_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $converted_slugs = [];
        $map = [
            "White House" => "white-house",
            "Penthouse" => "penthouse",
            "Aqua Class" => "aqua-class",
            "Heartsuite" => "heartsuite",
            "Steph's Skylounge 842/844" => "stephs-skylounge",
            "Steph's 848" => "stephs-848",
            "Steph's 846" => "stephs-846",
            "Concierge 817" => "concierge-817",
            "De Luxe" => "de-luxe",
            "Concierge 815/819" => "concierge-815-819",
            "Premium 840" => "premium-840",
            "Beatrice A" => "beatrice-a",
            "Premium 838" => "premium-838",
            "Giant Kubo" => "giant-kubo",
            "Seaside (Whole)" => "seaside-whole",
            "Beatrice B" => "beatrice-b",
            "Seaside (Half)" => "seaside-half",
            "Bamboo Kubo" => "bamboo-kubo"
        ];

        foreach ($db_names as $name) {
            $cleanName = trim($name);
            if (isset($map[$cleanName])) {
                $converted_slugs[] = $map[$cleanName];
            } else {
                $converted_slugs[] = strtolower(str_replace(' ', '-', $cleanName));
            }
        }
        
        echo json_encode(['booked_cottages' => $converted_slugs]);
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['booked_cottages' => [], 'error' => 'Database error']);
    }
}
?>