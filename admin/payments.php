<?php
session_start();
$current_page = 'payments';
include '../config.php'; 

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $pendingStmt = $db->prepare("SELECT * FROM bookings WHERE payment_method = 'pay-now' AND status = 'pending' AND deleted = 0 ORDER BY timestamp ASC");
    $pendingStmt->execute();
    $pendingPayments = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $historyStmt = $db->prepare("SELECT * FROM bookings WHERE (status = 'confirmed' OR payment_method = 'face-to-face') AND deleted = 0 ORDER BY timestamp DESC LIMIT 20");
    $historyStmt->execute();
    $paymentHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Counter for badge
    $pendingCount = count($pendingPayments);
    
} catch (PDOException $e) {
    $pendingPayments = [];
    $paymentHistory = [];
    $pendingCount = 0;
    error_log("Payments data error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        /* Action Buttons */
        .action-btn-group {
            display: flex;
            gap: 5px;
        }
        
        .btn-verify {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-approve {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .btn-approve:hover { background-color: #c3e6cb; }
        
        .btn-reject {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-reject:hover { background-color: #f5c6cb; }
        
        /* Table Styles */
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .payment-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            color: #495057;
            font-weight: 600;
            font-size: 0.9rem;
            border-bottom: 2px solid #e9ecef;
        }
        
        .payment-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
            vertical-align: middle;
        }
        
        .payment-table tr:hover { background-color: #f8f9fa; }
        
        /* Badges */
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .payment-method-badge {
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        .pm-gcash { background: #e3f2fd; color: #0d47a1; }

        .view-proof-btn {
            background: #1e3c72;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .view-proof-btn:hover { background: #2a5298; }
        .view-proof-btn:disabled { background: #6c757d; cursor: not-allowed; }

        /* Pending Alert Box */
        .pending-alert {
            background: linear-gradient(135deg, #ff9966, #ff5e62);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 5px 15px rgba(255, 94, 98, 0.3);
        }
        
        /* Modal Styles (Same as before) */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content-receipt {
            background: white;
            border-radius: 12px;
            width: 95%;
            max-width: 1000px;
            max-height: 95vh;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
        }
        
        .modal-header-receipt {
            background: #1e3c72;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .close-modal-receipt {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .modal-body-receipt {
            flex: 1;
            overflow: auto;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .receipt-image-zoom {
            max-width: 100%;
            max-height: 80vh;
            transition: transform 0.3s ease;
            cursor: zoom-in;
        }
        .receipt-image-zoom.zoomed { transform: scale(1.5); cursor: zoom-out; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-water"></i> Admin Panel</div>
        <ul class="menu-list">
            <li class="menu-item"><a href="dashboard.php" class="menu-link"><i class="fas fa-th-large"></i> Overview</a></li>
            <li class="menu-item"><a href="bookings.php" class="menu-link"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li class="menu-item"><a href="analytics.php" class="menu-link"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li class="menu-item"><a href="revenue.php" class="menu-link"><i class="fas fa-coins"></i> Revenue</a></li>
            <li class="menu-item"><a href="payments.php" class="menu-link active"><i class="fas fa-receipt"></i> Payments</a></li>
            <li class="menu-item"><a href="availability.php" class="menu-link"><i class="fas fa-door-open"></i> Availability</a></li>
            <li class="menu-item" style="margin-top: 2rem;"><a href="logout.php" class="menu-link" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h2>Payment Verification</h2>
            <div class="user-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 600;">Admin</div>
                    <div style="font-size: 0.8rem; color: #777;">Administrator</div>
                </div>
                <div class="admin-avatar">A</div>
            </div>
        </div>

        <?php if ($pendingCount > 0): ?>
        <div class="pending-alert">
            <div>
                <h3 style="margin:0; font-size: 1.5rem;"><i class="fas fa-bell"></i> Action Required</h3>
                <p style="margin:5px 0 0 0; opacity: 0.9;">You have <strong><?php echo $pendingCount; ?></strong> payment(s) waiting for verification.</p>
            </div>
            <i class="fas fa-arrow-down" style="font-size: 2rem; opacity: 0.5;"></i>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <h3 style="color: #d35400; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-clock"></i> Payments To Verify
            </h3>
            
            <?php if (count($pendingPayments) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="payment-table" style="border-top: 3px solid #d35400;">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Guest</th>
                                <th>Reference No.</th>
                                <th>Amount</th>
                                <th>Receipt</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pendingPayments as $row): ?>
                            <tr>
                                <td><strong><?php echo $row['booking_id']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($row['name']); ?><br>
                                    <small style="color:#666;"><?php echo htmlspecialchars($row['phone']); ?></small>
                                </td>
                                <td>
                                    <?php if($row['payment_reference']): ?>
                                        <code style="background:#eee; padding:2px 5px; border-radius:4px;"><?php echo htmlspecialchars($row['payment_reference']); ?></code>
                                    <?php else: ?>
                                        <span style="color:#999;">--</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight:bold; color:#27ae60;">₱<?php echo number_format($row['total_price'], 2); ?></td>
                                <td>
                                    <?php if (!empty($row['receipt_filename'])): ?>
                                        <button class="view-proof-btn" onclick="viewReceipt('<?php echo htmlspecialchars($row['receipt_filename']); ?>', '<?php echo htmlspecialchars($row['booking_id']); ?>', '<?php echo htmlspecialchars($row['name']); ?>')">
                                            <i class="fas fa-eye"></i> View Receipt
                                        </button>
                                    <?php else: ?>
                                        <span style="color:#dc3545; font-size:0.85rem;"><i class="fas fa-times-circle"></i> No Receipt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btn-group">
                                        <form method="POST" action="process_booking.php" onsubmit="return confirm('Are you sure you want to CONFIRM this payment?');">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <button type="submit" class="btn-verify btn-approve" title="Confirm Payment">
                                                <i class="fas fa-check"></i> Confirm
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="process_booking.php" onsubmit="return confirm('Are you sure you want to REJECT this payment?');">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <button type="submit" class="btn-verify btn-reject" title="Reject Payment">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 10px; color: #28a745;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h3>All Caught Up!</h3>
                    <p>No pending payments waiting for verification.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-container" style="margin-top: 40px; opacity: 0.8;">
            <h3 style="color: #666; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-history"></i> Recent Payment History
            </h3>
            
            <div style="overflow-x: auto;">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Guest</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paymentHistory) > 0): ?>
                            <?php foreach($paymentHistory as $row): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <?php if($row['payment_method'] == 'pay-now'): ?>
                                        <span class="payment-method-badge pm-gcash"><i class="fas fa-mobile-alt"></i> GCash</span>
                    
                                    <?php endif; ?>
                                </td>
                                <td>₱<?php echo number_format($row['total_price'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['receipt_filename'])): ?>
                                        <button class="view-proof-btn" style="padding: 4px 8px; font-size: 0.75rem;" onclick="viewReceipt('<?php echo htmlspecialchars($row['receipt_filename']); ?>', '<?php echo htmlspecialchars($row['booking_id']); ?>', '<?php echo htmlspecialchars($row['name']); ?>')">
                                            View
                                        </button>
                                    <?php else: ?>
                                        <span style="color:#ccc;">--</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;">No history yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="receiptModal" class="modal-overlay">
        <div class="modal-content-receipt">
            <div class="modal-header-receipt">
                <h3><i class="fas fa-receipt"></i> Payment Receipt</h3>
                <button class="close-modal-receipt" onclick="closeReceiptModal()">&times;</button>
            </div>
            <div class="modal-body-receipt">
                <div id="imageContainer" style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
                    </div>
            </div>
        </div>
    </div>

    <script>
        function viewReceipt(filename, bookingId, guestName) {
            const receiptPath = '../uploads/receipts/' + filename;
            const container = document.getElementById('imageContainer');
            
            // Check file extension
            const ext = filename.split('.').pop().toLowerCase();
            
            if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                container.innerHTML = `<img src="${receiptPath}" class="receipt-image-zoom" onclick="this.classList.toggle('zoomed')">`;
            } else {
                container.innerHTML = `
                    <div style="text-align:center; padding: 20px;">
                        <i class="fas fa-file-alt" style="font-size: 4rem; color: #ccc;"></i>
                        <p style="margin-top: 15px;">File type: ${ext.toUpperCase()}</p>
                        <a href="${receiptPath}" download class="view-proof-btn" style="display:inline-block; margin-top:10px;">
                            <i class="fas fa-download"></i> Download File
                        </a>
                    </div>`;
            }
            
            document.getElementById('receiptModal').style.display = 'flex';
        }

        function closeReceiptModal() {
            document.getElementById('receiptModal').style.display = 'none';
        }

        // Close modal on outside click
        document.getElementById('receiptModal').addEventListener('click', function(e) {
            if (e.target === this) closeReceiptModal();
        });
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeReceiptModal();
        });
    </script>
</body>
</html>