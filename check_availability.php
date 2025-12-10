<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cottage = $_POST['cottage'];
    $date = $_POST['date'];
    
    try {
        $query = "SELECT COUNT(*) as count FROM cottage_availability 
                  WHERE cottage_name = :cottage 
                  AND booked_date = :date 
                  AND status = 'confirmed'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":cottage", $cottage);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'available' => $result['count'] == 0,
            'cottage' => $cottage,
            'date' => $date
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'available' => false,
            'error' => 'Database error'
        ]);
    }
}
?>