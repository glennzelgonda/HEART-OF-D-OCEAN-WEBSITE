<?php
session_start();
$current_page = 'bookings';
include '../config.php'; 

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Normalize Cottage Names - IMPROVED FUNCTION (Matches availability.php)
function normalizeCottageName($name) {
    $name = trim($name);
    
    // If it's already a display name with price, return as is
    if (strpos($name, '—') !== false || strpos($name, '₱') !== false) {
        return $name;
    }
    
    // Map slug names to display names WITH PRICE
    $nameMap = [
        'stephs-848' => "Steph's 848 — ₱10,800",
        'stephs-846' => "Steph's 846 — ₱10,000",
        'premium-838' => 'Premium 838 — ₱7,800',
        'premium-840' => 'Premium 840 — ₱8,800',
        'beatrice-a' => 'Beatrice A — ₱7,800',
        'beatrice-b' => 'Beatrice B — ₱6,800',
        'concierge-817' => 'Concierge 817 — ₱9,800',
        'concierge-815-819' => 'Concierge 815/819 — ₱8,800',
        'de-luxe' => 'De Luxe — ₱8,800',
        'aqua-class' => 'Aqua Class — ₱11,800',
        'heartsuite' => 'Heartsuite — ₱11,800',
        'penthouse' => 'Penthouse — ₱12,800',
        'white-house' => 'White House — ₱30,000',
        'giant-kubo' => 'Giant Kubo — ₱6,800',
        'seaside-whole' => 'Seaside (Whole) — ₱6,800',
        'seaside-half' => 'Seaside (Half) — ₱3,400',
        'bamboo-kubo' => 'Bamboo Kubo — ₱2,800',
        'stephs-skylounge-842-844' => "Steph's Skylounge 842/844 — ₱11,800",
        'stephs-skylounge' => "Steph's Skylounge 842/844 — ₱11,800"
    ];
    
    $lowerName = strtolower($name);
    foreach ($nameMap as $slug => $display) {
        if ($lowerName === $slug) {
            return $display;
        }
    }
    
    // Try partial match for inconsistent database entries
    foreach ($nameMap as $slug => $display) {
        if (strpos($lowerName, $slug) !== false) {
            return $display;
        }
    }
    
    return $name; // Return original if no match
}

// Fetch Bookings
try {
    // Get filter status from URL, default to 'all'
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    $query = "SELECT * FROM bookings WHERE deleted = 0";
    $params = [];
    
    if ($status_filter !== 'all') {
        $query .= " AND status = :status";
        $params[':status'] = $status_filter;
    }
    
    $query .= " ORDER BY timestamp DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process data for display
    foreach ($bookings as &$booking) {
        $booking['room_display'] = normalizeCottageName($booking['room']);
        $booking['price_display'] = '₱' . number_format($booking['total_price'], 2);
        
        // Date formatting
        $checkin = new DateTime($booking['date']);
        $booking['checkin_display'] = $checkin->format('M d, Y');
        
        if ($booking['checkout_date'] && $booking['checkout_date'] != '0000-00-00') {
            $checkout = new DateTime($booking['checkout_date']);
            $booking['checkout_display'] = $checkout->format('M d, Y');
            $interval = $checkin->diff($checkout);
            $booking['nights_count'] = $interval->days ?: 1;
        } else {
            $booking['checkout_display'] = $booking['checkin_display'];
            $booking['nights_count'] = 1;
        }
        
        // Ensure guests field exists
        if (!isset($booking['guests']) || empty($booking['guests'])) {
            $booking['guests'] = 1;
        }
        if (!isset($booking['children'])) {
            $booking['children'] = 0;
        }
    }
    
    // Get counts for tabs
    $countStmt = $db->query("SELECT status, COUNT(*) as count FROM bookings WHERE deleted = 0 GROUP BY status");
    $counts = ['all' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0];
    while($row = $countStmt->fetch(PDO::FETCH_ASSOC)) {
        $status = strtolower($row['status']);
        if(isset($counts[$status])) {
            $counts[$status] = $row['count'];
        }
        $counts['all'] += $row['count'];
    }
    
} catch (PDOException $e) {
    $bookings = [];
    $counts = ['all' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0];
    error_log("Bookings fetch error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 5px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            background: white;
            border-radius: 30px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: 1px solid #eee;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-tab:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .filter-tab.active {
            background: #1e3c72;
            color: white;
            border-color: #1e3c72;
            box-shadow: 0 4px 10px rgba(30, 60, 114, 0.3);
        }
        
        .count-badge {
            background: rgba(0,0,0,0.1);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
        }
        
        .filter-tab.active .count-badge {
            background: rgba(255,255,255,0.2);
        }

        /* Status Badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            text-transform: capitalize;
        }
        .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-confirmed { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Payment Icons */
        .pay-icon {
            font-size: 1.1rem;
            margin-right: 5px;
            vertical-align: middle;
        }
        .pay-gcash { color: #007bff; }
        .has-receipt { color: #28a745; margin-left: 5px; cursor: help; }

        /* Table Styling */
        .clean-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .clean-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #444;
            border-bottom: 2px solid #e9ecef;
        }
        
        .clean-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #555;
            vertical-align: middle;
        }
        
        .clean-table tr:hover {
            background-color: #f8fbff;
        }

        /* Action Button */
        .btn-view {
            background: white;
            border: 1px solid #ddd;
            color: #555;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
        }
        
        .btn-view:hover {
            background: #1e3c72;
            color: white;
            border-color: #1e3c72;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            width: 100%;
            max-width: 500px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            background: #1e3c72;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 25px;
            overflow-y: auto;
        }

        .info-group {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .info-label { font-size: 0.85rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 1rem; color: #333; font-weight: 500; margin-top: 2px; }

        .receipt-preview {
            margin-top: 10px;
            border: 2px dashed #ddd;
            padding: 10px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
        }
        .receipt-preview img { max-width: 100%; max-height: 200px; border-radius: 5px; }
        .receipt-preview:hover { border-color: #1e3c72; background: #f8fbff; }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-water"></i> Admin Panel</div>
        <ul class="menu-list">
            <li class="menu-item"><a href="dashboard.php" class="menu-link"><i class="fas fa-th-large"></i> Overview</a></li>
            <li class="menu-item"><a href="bookings.php" class="menu-link active"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li class="menu-item"><a href="analytics.php" class="menu-link"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li class="menu-item"><a href="revenue.php" class="menu-link"><i class="fas fa-coins"></i> Revenue</a></li>
            <li class="menu-item"><a href="availability.php" class="menu-link"><i class="fas fa-door-open"></i> Availability</a></li>
            <li class="menu-item" style="margin-top: 2rem;"><a href="logout.php" class="menu-link" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h2>Booking Management</h2>
            <div class="user-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 600;">Admin</div>
                    <div style="font-size: 0.8rem; color: #777;">Administrator</div>
                </div>
                <div class="admin-avatar">A</div>
            </div>
        </div>

        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert-card <?php echo $_SESSION['alert']['type']; ?>">
                <div class="alert-icon">
                    <i class="fas <?php echo $_SESSION['alert']['icon']; ?>"></i>
                </div>
                <div class="alert-content">
                    <?php echo $_SESSION['alert']['message']; ?>
                </div>
                <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>

        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?php echo $status_filter == 'all' ? 'active' : ''; ?>">
                All Bookings <span class="count-badge"><?php echo $counts['all']; ?></span>
            </a>
            <a href="?status=pending" class="filter-tab <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock" style="color: #f39c12;"></i> Pending <span class="count-badge"><?php echo $counts['pending']; ?></span>
            </a>
            <a href="?status=confirmed" class="filter-tab <?php echo $status_filter == 'confirmed' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle" style="color: #28a745;"></i> Confirmed <span class="count-badge"><?php echo $counts['confirmed']; ?></span>
            </a>
            <a href="?status=cancelled" class="filter-tab <?php echo $status_filter == 'cancelled' ? 'active' : ''; ?>">
                <i class="fas fa-times-circle" style="color: #dc3545;"></i> Cancelled <span class="count-badge"><?php echo $counts['cancelled']; ?></span>
            </a>
        </div>

        <div class="table-container">
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <div class="search-box">
                    <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search guest name..." style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; width: 250px;">
                </div>
            </div>

            <?php if (count($bookings) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="clean-table" id="bookingTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Guest</th>
                            <th>Cottage</th>
                            <th>Check-in</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $row): ?>
                        <tr>
                            <td><strong><?php echo $row['booking_id']; ?></strong></td>
                            <td>
                                <div><?php echo htmlspecialchars($row['name']); ?></div>
                                <small style="color: #888;"><?php echo htmlspecialchars($row['phone']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['room_display']); ?></td>
                            <td><?php echo $row['checkin_display']; ?></td>
                            <td>
                                <?php if($row['payment_method'] == 'pay-now'): ?>
                                    <i class="fas fa-mobile-alt pay-icon pay-gcash" title="GCash"></i> GCash
                                <?php else: ?>
                                    <i class="fas fa-walking pay-icon" title="Face to Face" style="color: #6c757d;"></i> Walk-in
                                <?php endif; ?>
                                <?php if(!empty($row['receipt_filename'])): ?>
                                    <i class="fas fa-paperclip has-receipt" title="Receipt Uploaded"></i>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 600; color: #2e7d32;"><?php echo $row['price_display']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-view" onclick='openModal(<?php echo htmlspecialchars(json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8'); ?>)'>
                                    Details <i class="fas fa-chevron-right"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 10px;"></i>
                    <p>No bookings found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div id="bookingModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Booking Details</h3>
                <button onclick="closeModal()" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="info-group">
                    <div class="info-label">Guest Info</div>
                    <div class="info-value" id="mName"></div>
                    <div style="font-size: 0.9rem; color: #555;" id="mContact"></div>
                    <div style="font-size: 0.9rem; color: #555;" id="mEmail"></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Reservation</div>
                    <div class="info-value" id="mRoom"></div>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span>In: <strong id="mCheckin"></strong></span>
                        <span>Out: <strong id="mCheckout"></strong></span>
                    </div>
                    <div style="font-size: 0.9rem; color: #555; margin-top: 5px;">
                        <span id="mNights"></span> nights, <span id="mGuests"></span> guests
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Payment</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="info-value" id="mTotal"></div>
                        <div id="mMethodBadge" style="font-size: 0.8rem; background: #eee; padding: 3px 8px; border-radius: 4px;"></div>
                    </div>
                    
                    <div id="receiptSection" style="display: none; margin-top: 10px;">
                        <div class="info-label">Proof of Payment</div>
                        <div class="receipt-preview" onclick="viewFullImage()">
                            <img id="mReceiptImg" src="" alt="Receipt">
                            <div style="font-size: 0.8rem; color: #1e3c72; margin-top: 5px;">Click to view full size</div>
                        </div>
                        <div id="mRefNo" style="margin-top: 5px; font-family: monospace; background: #f0f0f0; padding: 5px; border-radius: 4px; font-size: 0.9rem;"></div>
                    </div>
                </div>

                <form method="POST" action="process_booking.php" style="display: grid; gap: 10px;">
                    <input type="hidden" name="booking_id" id="mBookingId">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <button type="submit" name="status" value="confirmed" class="btn-view" style="justify-content: center; background: #28a745; color: white; border: none;">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                        <button type="submit" name="status" value="cancelled" class="btn-view" style="justify-content: center; background: #dc3545; color: white; border: none;">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="delete_booking" value="1" class="btn-view" style="justify-content: center; background: #6c757d; color: white; border: none;" onclick="return confirm('Delete this booking permanently?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="imageModal" class="modal-overlay" style="z-index: 2000;" onclick="this.style.display='none'">
        <img id="fullImage" src="" style="max-width: 90%; max-height: 90vh; border-radius: 5px; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
    </div>

    <script>
        function openModal(data) {
            console.log('Opening modal with data:', data);
            
            // Parse if data is string
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch(e) {
                    console.error('Error parsing data:', e);
                    alert('Error loading booking details');
                    return;
                }
            }
            
            // Set modal values
            document.getElementById('mBookingId').value = data.booking_id || '';
            document.getElementById('mName').textContent = data.name || '';
            document.getElementById('mContact').textContent = data.phone || '';
            document.getElementById('mEmail').textContent = data.email || '';
            document.getElementById('mRoom').textContent = data.room_display || data.room || '';
            document.getElementById('mCheckin').textContent = data.checkin_display || data.date || '';
            document.getElementById('mCheckout').textContent = data.checkout_display || data.checkout_date || data.date || '';
            
            // Nights calculation
            let nights = data.nights_count || data.nights || 1;
            document.getElementById('mNights').textContent = nights;
            
            // Guests display
            let guests = (data.guests || 1) + " Adult(s)";
            if(data.children && parseInt(data.children) > 0) {
                guests += ", " + data.children + " Child(ren)";
            }
            document.getElementById('mGuests').textContent = guests;
            
            // Total price
            let total = '₱0.00';
            if (data.price_display) {
                total = data.price_display;
            } else if (data.total_price) {
                total = '₱' + parseFloat(data.total_price).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            document.getElementById('mTotal').textContent = total;
            
            // Payment method
            const paymentMethod = data.payment_method || 'face-to-face';
            const methodText = paymentMethod === 'pay-now' ? 'GCash' : 'Walk-in';
            document.getElementById('mMethodBadge').textContent = methodText;

            // Handle Receipt
            const receiptSection = document.getElementById('receiptSection');
            if(data.receipt_filename && data.receipt_filename.trim() !== '') {
                receiptSection.style.display = 'block';
                const imgSrc = '../uploads/receipts/' + data.receipt_filename;
                document.getElementById('mReceiptImg').src = imgSrc;
                document.getElementById('mRefNo').textContent = 'Ref: ' + (data.payment_reference || 'N/A');
                document.getElementById('fullImage').src = imgSrc;
            } else {
                receiptSection.style.display = 'none';
            }

            document.getElementById('bookingModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function viewFullImage() {
            document.getElementById('imageModal').style.display = 'flex';
        }

        function searchTable() {
            let input = document.getElementById("searchInput").value.toUpperCase();
            let table = document.getElementById("bookingTable");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td")[1]; // Guest Name Column
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
                }
            }
        }

        // Close modal on outside click
        window.onclick = function(event) {
            if (event.target == document.getElementById('bookingModal')) {
                closeModal();
            }
            if (event.target == document.getElementById('imageModal')) {
                document.getElementById('imageModal').style.display = 'none';
            }
        }
        
        // Close with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                document.getElementById('imageModal').style.display = 'none';
            }
        });
    </script>
</body>
</html>