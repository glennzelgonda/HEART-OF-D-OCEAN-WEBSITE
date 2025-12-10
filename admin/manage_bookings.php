<?php
include 'auth_check.php'; // Your existing admin authentication
include '../config.php';

// Handle status updates
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    
    if ($stmt->execute()) {
        $message = "Booking status updated successfully!";
    }
    $stmt->close();
}

// Get all bookings
$bookings = $conn->query("SELECT * FROM bookings ORDER BY timestamp DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Bookings - Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .bookings-table th, .bookings-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .bookings-table th {
            background-color: #f2f2f2;
        }
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-confirmed { color: #27ae60; font-weight: bold; }
        .status-canceled { color: #e74c3c; font-weight: bold; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <h1>Manage Bookings</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <table class="bookings-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Guests</th>
                    <th>Nights</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($booking = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $booking['booking_id']; ?></td>
                    <td><?php echo $booking['name']; ?></td>
                    <td><?php echo $booking['email']; ?></td>
                    <td><?php echo $booking['phone']; ?></td>
                    <td><?php echo $booking['room']; ?></td>
                    <td><?php echo $booking['date']; ?></td>
                    <td><?php echo $booking['checkout_date']; ?></td>
                    <td><?php echo $booking['guests']; ?></td>
                    <td><?php echo $booking['nights']; ?></td>
                    <td>$<?php echo $booking['total_price']; ?></td>
                    <td class="status-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="canceled" <?php echo $booking['status'] == 'canceled' ? 'selected' : ''; ?>>Canceled</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>