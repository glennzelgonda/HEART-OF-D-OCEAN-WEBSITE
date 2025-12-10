<?php
session_start();
include '../config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function setAlert($type, $message, $icon) {
    $_SESSION['alert'] = [
        'type'    => $type,    
        'message' => $message,
        'icon'    => $icon     
    ];
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setAlert('error', 'Invalid request method.', 'fa-exclamation-triangle');
    header("Location: bookings.php");
    exit();
}

// CSRF protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    setAlert('error', 'Security error: Invalid form submission.', 'fa-shield-alt');
    header("Location: bookings.php");
    exit();
}

// Check if booking_id exists
if (!isset($_POST['booking_id'])) {
    setAlert('error', 'No booking ID provided.', 'fa-search-minus');
    header("Location: bookings.php");
    exit();
}

$bookingId = trim($_POST['booking_id']);
function normalizeCottageForAvailability($cottageName) {
    $cottageName = trim($cottageName);
    
    if (strpos($cottageName, '—') !== false || strpos($cottageName, '₱') !== false) {
        $parts = explode('—', $cottageName);
        if (count($parts) > 1) {
            return trim($parts[0]);
        }
        $parts = explode('- ₱', $cottageName);
        if (count($parts) > 1) {
            return trim($parts[0]);
        }
    }
    
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
    
    // Check if it's a slug
    $lowerName = strtolower($cottageName);
    foreach ($slugMap as $slug => $display) {
        if ($lowerName === $slug) {
            return $display;
        }
    }
    
    // Try to match partial names from database
    foreach ($slugMap as $slug => $display) {
        if (strpos($lowerName, $slug) !== false) {
            return $display;
        }
    }
    
    // Clean up any remaining price info
    $cottageName = preg_replace('/\s*[—-]\s*₱\s*\d+[,\d\.]*/', '', $cottageName);
    $cottageName = preg_replace('/- ₱\d+[,\d\.]*/', '', $cottageName);
    
    return trim($cottageName);
}

// Function to update cottage_availability table - FIXED VERSION
function updateCottageAvailability($db, $bookingId, $bookingData, $action) {
    try {
        $cottageName = normalizeCottageForAvailability($bookingData['room']);
        $checkinDate = $bookingData['date'];
        $checkoutDate = $bookingData['checkout_date'];
        
        if (empty($checkoutDate) || $checkoutDate == '0000-00-00') {
            $checkoutDate = $checkinDate;
        }
        
        $start = new DateTime($checkinDate);
        $end = new DateTime($checkoutDate);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day')); // Include checkout date
        
        if ($action === 'add') {
            foreach ($dateRange as $date) {
                $bookedDate = $date->format('Y-m-d');
                
                // First, check if this booking already exists in availability
                $checkQuery = "SELECT id FROM cottage_availability WHERE cottage_name = :cottage_name AND booked_date = :booked_date AND booking_id = :booking_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->execute([
                    ':cottage_name' => $cottageName, 
                    ':booked_date' => $bookedDate,
                    ':booking_id' => $bookingId
                ]);
                
                // If not exists, insert it
                if (!$checkStmt->fetch()) {
                    // Also delete any existing entry for same cottage/date (to avoid duplicates)
                    $cleanupQuery = "DELETE FROM cottage_availability WHERE cottage_name = :cottage_name AND booked_date = :booked_date AND booking_id != :booking_id";
                    $cleanupStmt = $db->prepare($cleanupQuery);
                    $cleanupStmt->execute([
                        ':cottage_name' => $cottageName, 
                        ':booked_date' => $bookedDate,
                        ':booking_id' => $bookingId
                    ]);
                    
                    // Insert new availability
                    $insertQuery = "INSERT INTO cottage_availability (cottage_name, booked_date, status, booking_id) VALUES (:cottage_name, :booked_date, 'confirmed', :booking_id)";
                    $insertStmt = $db->prepare($insertQuery);
                    $insertStmt->execute([
                        ':cottage_name' => $cottageName, 
                        ':booked_date' => $bookedDate, 
                        ':booking_id' => $bookingId
                    ]);
                }
            }
            return " (Dates added to calendar)";
            
        } elseif ($action === 'remove') {
            foreach ($dateRange as $date) {
                $bookedDate = $date->format('Y-m-d');
                
                // DELETE with exact match (not LIKE)
                $deleteQuery = "DELETE FROM cottage_availability WHERE cottage_name = :cottage_name AND booked_date = :booked_date AND booking_id = :booking_id";
                $deleteStmt = $db->prepare($deleteQuery);
                $deleteStmt->execute([
                    ':cottage_name' => $cottageName,
                    ':booked_date' => $bookedDate,
                    ':booking_id' => $bookingId
                ]);
            }
            return " (Dates removed from calendar)";
        }
        
        return "";
    } catch (Exception $e) {
        error_log("Cottage availability update error: " . $e->getMessage());
        return " (Calendar update error)";
    }
}

// Function to sync ALL confirmed bookings to calendar (for fixing existing data)
function syncAllConfirmedBookingsToCalendar($db) {
    try {
        $query = "SELECT * FROM bookings WHERE status = 'confirmed' AND deleted = 0";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $confirmedBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $synced = 0;
        foreach ($confirmedBookings as $booking) {
            updateCottageAvailability($db, $booking['booking_id'], $booking, 'add');
            $synced++;
        }
        
        return $synced;
    } catch (Exception $e) {
        error_log("Sync all bookings error: " . $e->getMessage());
        return 0;
    }
}

// ==========================================
// HANDLE STATUS UPDATE
// ==========================================
if (isset($_POST['status'])) {
    $newStatus = trim($_POST['status']);
    $validStatuses = ['confirmed', 'cancelled', 'pending'];
    
    if (!in_array($newStatus, $validStatuses)) {
        setAlert('error', 'Invalid status requested.', 'fa-times-circle');
        header("Location: bookings.php");
        exit();
    }
    
    $emailSent = false;
    $availabilityMessage = '';
    
    try {
        $db->beginTransaction();
        
        $query = "SELECT * FROM bookings WHERE booking_id = :booking_id AND deleted = 0";
        $stmt = $db->prepare($query);
        $stmt->execute([':booking_id' => $bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            setAlert('error', 'Booking not found or already deleted.', 'fa-search-minus');
            header("Location: bookings.php");
            exit();
        }
        
        $oldStatus = $booking['status'];
        
        // Update Calendar Availability
        if ($newStatus === 'confirmed') {
            // Add dates to calendar
            $availabilityMessage = updateCottageAvailability($db, $bookingId, $booking, 'add');
        } elseif (($oldStatus === 'confirmed') && ($newStatus !== 'confirmed')) {
            // Remove dates from calendar (cancelled or pending)
            $availabilityMessage = updateCottageAvailability($db, $bookingId, $booking, 'remove');
        } elseif ($oldStatus === 'confirmed' && $newStatus === 'confirmed') {
            // Already confirmed - ensure dates are in calendar (re-sync)
            $availabilityMessage = updateCottageAvailability($db, $bookingId, $booking, 'add');
        }
        
        // Update Booking Status
        $updateQuery = "UPDATE bookings SET status = :status WHERE booking_id = :booking_id AND deleted = 0";
        $stmt = $db->prepare($updateQuery);
        $stmt->execute([':status' => $newStatus, ':booking_id' => $bookingId]);
        
        $db->commit();
        
        // --- SEND EMAIL (Outside Transaction) ---
        $emailError = '';
        if (file_exists('../email_functions.php')) {
            include '../email_functions.php';
            
            $to = $booking['email'];
            $guestName = htmlspecialchars($booking['name']);
            $bookingIdDisplay = $booking['booking_id'];
            $cottageName = normalizeCottageForAvailability($booking['room']);
            $checkinDate = $booking['date'];
            $checkoutDate = $booking['checkout_date'];
            $nights = $booking['nights'] ?? 1;
            $totalPrice = number_format($booking['total_price'], 2);
            
            if ($newStatus === 'confirmed') {
                $subject = "Booking Confirmation - Heart Of D Ocean Beach Resort";
                // PROFESSIONAL CONFIRMATION TEMPLATE
                $message = "
                <!DOCTYPE html>
                <html>
                <body style='margin: 0; padding: 0; font-family: Helvetica, Arial, sans-serif; background-color: #f4f7f6;'>
                    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='padding: 40px 0;'>
                        <tr><td align='center'>
                            <table border='0' cellpadding='0' cellspacing='0' width='600' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                                <tr><td align='center' style='background-color: #1e3c72; padding: 40px 0;'>
                                    <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-transform: uppercase;'>Booking Confirmed</h1>
                                    <p style='color: #aab7cf; margin: 10px 0 0; font-size: 14px;'>Thank you for choosing Heart Of D Ocean</p>
                                </td></tr>
                                <tr><td style='padding: 40px;'>
                                    <p style='color: #555; font-size: 16px;'>Dear <strong>$guestName</strong>,</p>
                                    <p style='color: #555; font-size: 16px; line-height: 1.6;'>We are pleased to confirm your reservation. We have received your payment and secured your cottage.</p>
                                    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #f8f9fa; border-radius: 6px; border: 1px solid #eee; margin: 20px 0;'>
                                        <tr><td style='padding: 20px;'>
                                            <table width='100%'>
                                                <tr><td style='color: #888; font-size: 12px; font-weight: bold; text-transform: uppercase;'>Ref No.</td><td align='right' style='color: #1e3c72; font-family: monospace; font-weight: bold;'>$bookingIdDisplay</td></tr>
                                                <tr><td colspan='2' style='border-bottom: 1px solid #eee; height: 10px;'></td></tr>
                                                <tr><td style='padding-top: 10px; font-weight: bold; color: #555;'>$cottageName</td><td align='right' style='padding-top: 10px; color: #28a745; font-weight: bold;'>Confirmed</td></tr>
                                                <tr><td style='color: #777; font-size: 14px;'>Check-in</td><td align='right' style='color: #333; font-size: 14px;'>$checkinDate</td></tr>
                                                <tr><td style='color: #777; font-size: 14px;'>Check-out</td><td align='right' style='color: #333; font-size: 14px;'>$checkoutDate</td></tr>
                                                <tr><td style='padding-top: 10px; font-weight: bold; border-top: 1px dashed #ddd;'>Total</td><td align='right' style='padding-top: 10px; font-weight: bold; font-size: 18px; border-top: 1px dashed #ddd;'>₱$totalPrice</td></tr>
                                            </table>
                                        </td></tr>
                                    </table>
                                </td></tr>
                                <tr><td align='center' style='background-color: #f4f7f6; padding: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;'>Heart Of D Ocean Beach Resort</td></tr>
                            </table>
                        </td></tr>
                    </table>
                </body>
                </html>";
            } else {
                // PROFESSIONAL UPDATE/CANCEL TEMPLATE
                $statusColor = ($newStatus === 'cancelled' || $newStatus === 'canceled') ? '#e74c3c' : '#f39c12';
                $statusTitle = ucfirst($newStatus);
                $subject = "Booking Status Update - Heart Of D Ocean Beach Resort";
                
                $message = "
                <!DOCTYPE html>
                <html>
                <body style='margin: 0; padding: 0; font-family: Helvetica, Arial, sans-serif; background-color: #f4f7f6;'>
                    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='padding: 40px 0;'>
                        <tr><td align='center'>
                            <table border='0' cellpadding='0' cellspacing='0' width='600' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                                <tr><td align='center' style='background-color: $statusColor; padding: 30px 0;'>
                                    <h1 style='color: #ffffff; margin: 0; font-size: 22px; text-transform: uppercase;'>Booking $statusTitle</h1>
                                </td></tr>
                                <tr><td style='padding: 40px;'>
                                    <p style='color: #555; font-size: 16px;'>Dear <strong>$guestName</strong>,</p>
                                    <p style='color: #555; font-size: 16px;'>This email is to notify you that the status of your booking has changed.</p>
                                    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #fcfcfc; border: 1px solid #eee; border-radius: 6px; margin: 20px 0;'>
                                        <tr><td style='padding: 20px;'>
                                            <p style='margin: 0 0 5px; color: #888; font-size: 12px; text-transform: uppercase;'>Booking Reference</p>
                                            <p style='margin: 0 0 20px; font-weight: bold; font-family: monospace; font-size: 16px;'>$bookingIdDisplay</p>
                                            <p style='margin: 0 0 5px; color: #888; font-size: 12px; text-transform: uppercase;'>New Status</p>
                                            <p style='margin: 0; color: $statusColor; font-weight: bold; font-size: 18px;'>$statusTitle</p>
                                        </td></tr>
                                    </table>
                                </td></tr>
                                <tr><td align='center' style='background-color: #f4f7f6; padding: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;'>Heart Of D Ocean Beach Resort</td></tr>
                            </table>
                        </td></tr>
                    </table>
                </body>
                </html>";
            }
            
            if (function_exists('sendResortEmail')) {
                $emailSent = sendResortEmail($to, $subject, $message);
                if (!$emailSent) $emailError = " (Email failed)";
            } else {
                $emailError = " (Email function error)";
            }
        }
        
        // SUCCESS ALERT 
        $statusDisplay = ['confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'pending' => 'Pending'];
        $displayStatus = $statusDisplay[$newStatus] ?? ucfirst($newStatus);
        
        $msg = "Booking <strong>#$bookingId</strong> for " . htmlspecialchars($booking['name']);
        $msg .= " has been updated to <strong>$displayStatus</strong>.";
        $msg .= $availabilityMessage;
        if ($emailSent) $msg .= " Notification email sent.";
        elseif ($emailError) $msg .= " <span style='color:red'>$emailError</span>";
        
        setAlert('success', $msg, 'fa-check-circle');
        
    } catch(PDOException $exception) {
        if ($db->inTransaction()) $db->rollBack();
        setAlert('error', "System Error: " . $exception->getMessage(), 'fa-exclamation-circle');
        error_log("Booking update error: " . $exception->getMessage());
    }
    
    header("Location: bookings.php");
    exit();
}

// ==========================================
// HANDLE DELETE BOOKING
// ==========================================
if (isset($_POST['delete_booking'])) {
    try {
        $query = "SELECT name, room, email, status FROM bookings WHERE booking_id = :booking_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':booking_id' => $bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            setAlert('error', 'Booking not found.', 'fa-search-minus');
            header("Location: bookings.php");
            exit();
        }
        
        $db->beginTransaction();
        
        // Remove from availability if it was confirmed
        if ($booking['status'] === 'confirmed') {
            updateCottageAvailability($db, $bookingId, $booking, 'remove');
        }
        
        // Soft delete
        $deleteQuery = "UPDATE bookings SET deleted = 1 WHERE booking_id = :booking_id";
        $stmt = $db->prepare($deleteQuery);
        $stmt->execute([':booking_id' => $bookingId]);
        
        $db->commit();
        
        // SEND DELETE EMAIL 
        $deleteEmailSent = false;
        if (file_exists('../email_functions.php') && function_exists('sendResortEmail')) {
            include '../email_functions.php';
            
            $subject = "Booking Deleted - Heart Of D Ocean Beach Resort";
            $message = "
            <!DOCTYPE html>
            <html>
            <body style='margin: 0; padding: 0; font-family: Helvetica, Arial, sans-serif; background-color: #f4f7f6;'>
                <table border='0' cellpadding='0' cellspacing='0' width='100%' style='padding: 40px 0;'>
                    <tr><td align='center'>
                        <table border='0' cellpadding='0' cellspacing='0' width='600' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                            <tr><td align='center' style='background-color: #dc3545; padding: 30px 0;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px; text-transform: uppercase;'>Booking Deleted</h1>
                            </td></tr>
                            <tr><td style='padding: 40px;'>
                                <p style='color: #555; font-size: 16px;'>Dear <strong>" . htmlspecialchars($booking['name']) . "</strong>,</p>
                                <p style='color: #555; font-size: 16px;'>Your booking has been removed from our system.</p>
                                <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #fef2f2; border: 1px solid #f5c6cb; border-radius: 6px; margin: 20px 0;'>
                                    <tr><td style='padding: 20px;'>
                                        <p style='margin: 0 0 5px; color: #888; font-size: 12px; text-transform: uppercase;'>Booking Reference</p>
                                        <p style='margin: 0 0 10px; font-weight: bold; font-family: monospace; font-size: 16px;'>$bookingId</p>
                                        <p style='margin: 0 0 5px; color: #888; font-size: 12px; text-transform: uppercase;'>Cottage</p>
                                        <p style='margin: 0; font-weight: bold; color: #333;'>" . htmlspecialchars($booking['room']) . "</p>
                                    </td></tr>
                                </table>
                                <p style='color: #777; font-size: 13px;'>If this was a mistake, please contact us immediately.</p>
                            </td></tr>
                            <tr><td align='center' style='background-color: #f4f7f6; padding: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;'>Heart Of D Ocean Beach Resort</td></tr>
                        </table>
                    </td></tr>
                </table>
            </body>
            </html>";
            
            $deleteEmailSent = sendResortEmail($booking['email'], $subject, $message);
        }
        
        // SUCCESS ALERT FOR DELETION
        $msg = "Booking <strong>#$bookingId</strong> has been deleted.";
        if ($deleteEmailSent) $msg .= " Notification email sent.";
        
        setAlert('success', $msg, 'fa-trash-alt');
        
        // Log deletion
        $deleteLog = "[" . date('Y-m-d H:i:s') . "] DELETED: ID: $bookingId | Name: " . htmlspecialchars($booking['name']) . "\n";
        @file_put_contents('../deletion_log.txt', $deleteLog, FILE_APPEND | LOCK_EX);
        
    } catch(PDOException $exception) {
        if ($db->inTransaction()) $db->rollBack();
        setAlert('error', "Error deleting booking: " . $exception->getMessage(), 'fa-exclamation-triangle');
        error_log("Delete booking error: " . $exception->getMessage());
    }
    
    header("Location: bookings.php");
    exit();
}

// ==========================================
// HANDLE SYNC ALL CONFIRMED BOOKINGS
// ==========================================
if (isset($_POST['sync_all_bookings'])) {
    $syncedCount = syncAllConfirmedBookingsToCalendar($db);
    
    if ($syncedCount > 0) {
        setAlert('success', "Successfully synced $syncedCount confirmed bookings to calendar.", 'fa-sync-alt');
    } else {
        setAlert('info', "No confirmed bookings to sync or sync completed.", 'fa-info-circle');
    }
    
    header("Location: bookings.php");
    exit();
}

// Fallback
setAlert('error', 'Invalid request parameters.', 'fa-question-circle');
header("Location: bookings.php");
exit();
?>