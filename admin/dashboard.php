<?php
session_start();
$current_page = 'overview';
include '../config.php'; 

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check for action messages
$action_message = '';
if (isset($_SESSION['action_message'])) {
    $action_message = $_SESSION['action_message'];
    unset($_SESSION['action_message']);
}

// Fetch Data Logic with deleted=0 filter
function getCount($db, $status = null) {
    try {
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = :status AND deleted = 0");
            $stmt->execute([':status' => $status]);
        } else {
            $stmt = $db->query("SELECT COUNT(*) as count FROM bookings WHERE deleted = 0");
        }
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch (PDOException $e) { 
        error_log("Get count error: " . $e->getMessage());
        return 0; 
    }
}

$total_bookings = getCount($db);
$pending_bookings = getCount($db, 'pending');
$confirmed_bookings = getCount($db, 'confirmed');
$cancelled_bookings = getCount($db, 'cancelled'); // We'll calculate but not display

// Total revenue from confirmed bookings
try {
    $stmt = $db->prepare("SELECT COALESCE(SUM(total_price), 0) as total FROM bookings WHERE status = 'confirmed' AND deleted = 0");
    $stmt->execute();
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
} catch (PDOException $e) { 
    $total_revenue = 0;
    error_log("Revenue error: " . $e->getMessage());
}

// Popular cottages
try {
    $pop_stmt = $db->prepare("SELECT room, COUNT(*) as count FROM bookings WHERE deleted = 0 GROUP BY room ORDER BY count DESC LIMIT 5");
    $pop_stmt->execute();
    $popular_cottages = $pop_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    $popular_cottages = [];
    error_log("Popular cottages error: " . $e->getMessage());
}

// Monthly stats
try {
    $current_year = date('Y');
    $month_stmt = $db->prepare("SELECT MONTHNAME(date) as month, COUNT(*) as count FROM bookings WHERE YEAR(date) = :year AND deleted = 0 GROUP BY MONTH(date) ORDER BY MONTH(date) ASC");
    $month_stmt->execute([':year' => $current_year]);
    $monthly_stats = $month_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    $monthly_stats = [];
    error_log("Monthly stats error: " . $e->getMessage());
}

// Get recent bookings for display
$recentBookingsData = [];
try {
    $recentQuery = "SELECT booking_id, name, room, date, status FROM bookings WHERE deleted = 0 ORDER BY timestamp DESC LIMIT 5";
    $recentStmt = $db->prepare($recentQuery);
    $recentStmt->execute();
    $recentBookingsData = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Recent bookings data error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .last-update { 
            color: #6c757d; 
            text-align: right; 
            margin-bottom: 1rem; 
            font-size: 0.9rem;
        }
        .action-message { 
            background: #d4edda; 
            color: #155724; 
            padding: 1rem; 
            border-radius: 5px; 
            margin-bottom: 1rem; 
        }
        /* Updated to show 4 cards instead of 6 */
        .stats-grid-extended {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .recent-bookings-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
        }
        .recent-table {
            width: 100%;
            border-collapse: collapse;
        }
        .recent-table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            color: #495057;
            font-weight: 600;
            font-size: 0.9rem;
            border-bottom: 2px solid #e9ecef;
        }
        .recent-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
        }
        .recent-table tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand"><i class="fas fa-water"></i> Admin Panel</div>
        <ul class="menu-list">
            <li class="menu-item"><a href="dashboard.php" class="menu-link active"><i class="fas fa-th-large"></i> Overview</a></li>
            <li class="menu-item"><a href="bookings.php" class="menu-link"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li class="menu-item"><a href="analytics.php" class="menu-link"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li class="menu-item"><a href="revenue.php" class="menu-link"><i class="fas fa-coins"></i> Revenue</a></li>
            <li class="menu-item"><a href="availability.php" class="menu-link"><i class="fas fa-door-open"></i> Availability</a></li>
            <li class="menu-item" style="margin-top: 2rem;"><a href="logout.php" class="menu-link" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h2>Dashboard Overview</h2>
            <div class="user-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 600;">Admin</div>
                    <div style="font-size: 0.8rem; color: #777;">Administrator</div>
                </div>
                <div class="admin-avatar">A</div>
            </div>
        </div>

        <?php if ($action_message): ?>
            <div class="action-message">
                <?php echo $action_message; ?>
            </div>
        <?php endif; ?>

        <div class="last-update">
            Last updated: <?php echo date('Y-m-d H:i:s'); ?>
        </div>

        <!-- MODIFIED: Removed "Cancelled" and "Last 7 Days" cards -->
        <div class="stats-grid-extended">
            <div class="stat-card revenue-card">
                <div class="stat-info"><h3>₱<?php echo number_format($total_revenue, 2); ?></h3><p>Total Revenue</p></div>
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="stat-card card-total">
                <div class="stat-info"><h3><?php echo $total_bookings; ?></h3><p>Total Bookings</p></div>
                <div class="stat-icon"><i class="fas fa-bookmark"></i></div>
            </div>
            <div class="stat-card card-pending">
                <div class="stat-info"><h3><?php echo $pending_bookings; ?></h3><p>Pending</p></div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="stat-card card-confirmed">
                <div class="stat-info"><h3><?php echo $confirmed_bookings; ?></h3><p>Confirmed</p></div>
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>

        <div class="data-grid">
            <div class="data-card">
                <div class="section-title"><i class="fas fa-calendar-alt"></i> Monthly Bookings (<?php echo date('Y'); ?>)</div>
                <div class="chart-container">
                    <?php if (count($monthly_stats) > 0): ?>
                        <?php 
                            $max_count = max(array_column($monthly_stats, 'count'));
                            if ($max_count == 0) $max_count = 1;
                        ?>
                        <?php foreach($monthly_stats as $stat): 
                            $percent = ($stat['count'] / $max_count) * 100;
                            $width = max($percent, 10); 
                        ?>
                        <div class="chart-row">
                            <div class="chart-label"><span><?php echo $stat['month']; ?></span><span><?php echo $stat['count']; ?></span></div>
                            <div class="progress-bg"><div class="progress-fill" style="width: <?php echo $width; ?>%;"></div></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align:center; color:#999; padding:20px;">No bookings yet for this year.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="data-card">
                <div class="section-title"><i class="fas fa-fire"></i> Top Cottages</div>
                <ul class="popular-list">
                    <?php if (count($popular_cottages) > 0): ?>
                        <?php foreach($popular_cottages as $cottage): ?>
                        <li class="popular-item">
                            <span class="cottage-name"><?php echo htmlspecialchars($cottage['room']); ?></span>
                            <span class="cottage-count"><?php echo $cottage['count']; ?></span>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="popular-item" style="color: #999; text-align: center; padding: 20px;">No cottage data available</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Recent Bookings Section -->
        <div class="recent-bookings-card">
            <h3><i class="fas fa-history"></i> Recent Bookings</h3>
            <?php if (count($recentBookingsData) > 0): ?>
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Guest Name</th>
                            <th>Cottage</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookingsData as $booking): ?>
                        <tr>
                            <td><strong><?php echo $booking['booking_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['room']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; color:#999; padding:20px;">No recent bookings found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>