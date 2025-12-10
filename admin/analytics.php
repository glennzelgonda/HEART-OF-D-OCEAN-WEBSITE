<?php
session_start();
$current_page = 'analytics';
include '../config.php'; 

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Get timeframe filter
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : '30'; 

// Data logic for charts
$current_year = date('Y');
$months = []; $booking_counts = [];
try {
    for ($m=1; $m<=12; $m++) {
        $month_name = date('F', mktime(0, 0, 0, $m, 1));
        $months[] = $month_name;
        $booking_counts[$month_name] = 0;
    }
    $stmt = $db->prepare("SELECT MONTHNAME(date) as month, COUNT(*) as count FROM bookings WHERE YEAR(date) = :year AND deleted = 0 GROUP BY MONTH(date)");
    $stmt->execute([':year' => $current_year]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
        $booking_counts[$row['month']] = $row['count']; 
    }
} catch (PDOException $e) { 
    error_log("Monthly booking error: " . $e->getMessage());
}

$status_counts = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0];
try {
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM bookings WHERE deleted = 0 GROUP BY status");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = strtolower($row['status']);
        if (isset($status_counts[$key])) $status_counts[$key] = $row['count'];
    }
} catch (PDOException $e) { 
    error_log("Status counts error: " . $e->getMessage());
}

$cottage_labels = []; $cottage_data = [];
try {
    $stmt = $db->prepare("SELECT room, COUNT(*) as count FROM bookings WHERE deleted = 0 GROUP BY room ORDER BY count DESC LIMIT 8");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cottage_labels[] = $row['room'];
        $cottage_data[] = $row['count'];
    }
} catch (PDOException $e) { 
    error_log("Cottage data error: " . $e->getMessage());
}

// Get weekly trends data based on timeframe
$weeklyTrends = [];
try {
    $interval = "INTERVAL $timeframe DAY";
    $weeklyTrendsQuery = "SELECT 
        DATE(timestamp) as date,
        COUNT(*) as daily_bookings
        FROM bookings 
        WHERE timestamp >= DATE_SUB(NOW(), $interval) AND deleted = 0
        GROUP BY DATE(timestamp)
        ORDER BY date";
    $weeklyStmt = $db->prepare($weeklyTrendsQuery);
    $weeklyStmt->execute();
    $weeklyTrends = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Weekly trends error: " . $e->getMessage());
}

// Prepare weekly trends for JavaScript
$weeklyDates = [];
$weeklyBookings = [];
foreach ($weeklyTrends as $trend) {
    $weeklyDates[] = $trend['date'];
    $weeklyBookings[] = (int)$trend['daily_bookings'];
}

if (empty($weeklyBookings)) {
    $weeklyDates = [date('Y-m-d', strtotime("-$timeframe days")), date('Y-m-d')];
    $weeklyBookings = [0, 0];
}

// Get total booking stats
try {
    $totalStmt = $db->prepare("SELECT COUNT(*) as total FROM bookings WHERE deleted = 0");
    $totalStmt->execute();
    $totalBookings = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $recentStmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND deleted = 0");
    $recentStmt->execute();
    $recentBookings = $recentStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    $totalBookings = 0;
    $recentBookings = 0;
}

// Get peak dates with enhanced data
$peakDates = [];
try {
    $peakDatesQuery = "SELECT 
        date, 
        COUNT(*) as booking_count,
        SUM(total_price) as total_revenue,
        ROUND(AVG(total_price), 2) as avg_per_booking
        FROM bookings 
        WHERE status = 'confirmed' AND deleted = 0
        GROUP BY date 
        ORDER BY booking_count DESC, total_revenue DESC 
        LIMIT 5";
    $peakDatesStmt = $db->prepare($peakDatesQuery);
    $peakDatesStmt->execute();
    $peakDates = $peakDatesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Peak dates error: " . $e->getMessage());
}

// Calculate quick stats
$quickStats = [
    'avg_bookings_per_day' => count($weeklyTrends) > 0 ? 
        round(array_sum($weeklyBookings) / count($weeklyTrends), 1) : 0,
    'busiest_day' => !empty($peakDates) ? date('F d', strtotime($peakDates[0]['date'])) : 'N/A',
    'avg_revenue_per_booking' => !empty($peakDates) ? 
        round(array_sum(array_column($peakDates, 'total_revenue')) / array_sum(array_column($peakDates, 'booking_count')), 2) : 0,
    'total_revenue' => !empty($peakDates) ? array_sum(array_column($peakDates, 'total_revenue')) : 0
];

// Get previous month for comparison
$previous_month_bookings = 0;
try {
    $prevStmt = $db->prepare("SELECT COUNT(*) as count FROM bookings 
        WHERE MONTH(date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
        AND YEAR(date) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        AND deleted = 0");
    $prevStmt->execute();
    $previous_month_bookings = $prevStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (PDOException $e) {
    error_log("Previous month error: " . $e->getMessage());
}

$current_month_bookings = $booking_counts[date('F')] ?? 0;
$month_over_month_growth = $previous_month_bookings > 0 ? 
    round((($current_month_bookings - $previous_month_bookings) / $previous_month_bookings) * 100, 1) : 
    ($current_month_bookings > 0 ? 100 : 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .last-update { 
            color: #6c757d; 
            text-align: right; 
            margin-bottom: 1rem; 
            font-size: 0.9rem;
        }
        
        /* Stats Summary (Boxes at top) */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s;
        }
        .stat-box:hover { transform: translateY(-5px); }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .stat-pending { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .stat-confirmed { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .stat-cancelled { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .stat-total { background: rgba(30, 60, 114, 0.1); color: #1e3c72; }
        .stat-growth { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        
        .stat-info h4 { margin: 0; color: #6c757d; font-size: 0.9rem; }
        .stat-number { font-size: 1.8rem; font-weight: 700; margin: 5px 0 0 0; color: #333; }
        .stat-change {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 5px;
        }
        .stat-change.positive { color: #28a745; }
        .stat-change.negative { color: #dc3545; }
        
        /* Timeframe Filter */
        .timeframe-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .timeframe-btn {
            padding: 8px 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        .timeframe-btn:hover {
            background: #f8f9fa;
        }
        .timeframe-btn.active {
            background: #1e3c72;
            color: white;
            border-color: #1e3c72;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn-print {
            background: #1e3c72;
            color: white;
        }
        .btn-export {
            background: #28a745;
            color: white;
        }
        .btn-export:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .btn-print:hover {
            background: #2a5298;
            transform: translateY(-2px);
        }
        
        /* Large Chart Cards */
        .data-card-extended {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .chart-container-small {
            height: 300px;
            position: relative;
        }
        
        /* Peak Dates Table */
        .peak-dates-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .peak-dates-table th { 
            text-align: left; 
            padding: 12px; 
            color: #666; 
            font-size: 0.9rem; 
            border-bottom: 2px solid #eee; 
            background: #f8f9fa;
        }
        .peak-dates-table td { 
            padding: 12px; 
            border-bottom: 1px solid #f5f5f5; 
        }
        .revenue-highlight { 
            color: #28a745; 
            font-weight: 700; 
        }
        .avg-price {
            font-size: 0.85rem;
            color: #666;
            margin-top: 3px;
        }
        
        /* Quick Stats */
        .quick-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .quick-stat {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .quick-stat-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }
        .quick-stat-value {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e3c72;
        }
        
        /* Loading Indicator */
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
            gap: 15px;
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .action-buttons, .timeframe-filter, .last-update { 
                display: none !important; 
            }
            .main-content { 
                margin-left: 0; 
                padding: 0; 
            }
            .data-card-extended, .chart-card, .stat-box, .quick-stat { 
                box-shadow: none; 
                border: 1px solid #ddd; 
                page-break-inside: avoid;
            }
            body { 
                background: white; 
            }
            h2, h3 {
                color: black !important;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Indicator -->
    <div id="loading" style="display: none;">
        <i class="fas fa-spinner fa-spin fa-3x" style="color: #1e3c72;"></i>
        <p>Loading analytics data...</p>
    </div>

    <aside class="sidebar">
        <div class="brand"><i class="fas fa-water"></i> Admin Panel</div>
        <ul class="menu-list">
            <li class="menu-item"><a href="dashboard.php" class="menu-link"><i class="fas fa-th-large"></i> Overview</a></li>
            <li class="menu-item"><a href="bookings.php" class="menu-link"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li class="menu-item"><a href="analytics.php" class="menu-link active"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li class="menu-item"><a href="revenue.php" class="menu-link"><i class="fas fa-coins"></i> Revenue</a></li>
            <li class="menu-item"><a href="availability.php" class="menu-link"><i class="fas fa-door-open"></i> Availability</a></li>
            <li class="menu-item" style="margin-top: 2rem;"><a href="logout.php" class="menu-link" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h2>Data Analytics Dashboard</h2>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="text-align: right;">
                    <div style="font-weight: 600; color: #1e3c72;">Admin</div>
                    <div style="font-size: 0.85rem; color: #7f8c8d;">Analytics Report</div>
                </div>
            </div>
        </div>

        <!-- Timeframe Filter -->
        <div class="timeframe-filter">
            <a href="?timeframe=7" class="timeframe-btn <?php echo $timeframe == '7' ? 'active' : ''; ?>">
                Last 7 Days
            </a>
            <a href="?timeframe=30" class="timeframe-btn <?php echo $timeframe == '30' ? 'active' : ''; ?>">
                Last 30 Days
            </a>
            <a href="?timeframe=90" class="timeframe-btn <?php echo $timeframe == '90' ? 'active' : ''; ?>">
                Last 3 Months
            </a>
            <a href="?timeframe=365" class="timeframe-btn <?php echo $timeframe == '365' ? 'active' : ''; ?>">
                Last Year
            </a>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="action-btn btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button class="action-btn btn-export" onclick="exportToCSV()">
                <i class="fas fa-download"></i> Export Data
            </button>
        </div>

        <div class="last-update">
            <i class="fas fa-sync-alt"></i> Report generated: <?php echo date('F d, Y h:i A'); ?>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats-grid">
            <div class="quick-stat">
                <div class="quick-stat-label">Avg. Daily Bookings</div>
                <div class="quick-stat-value"><?php echo $quickStats['avg_bookings_per_day']; ?></div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-label">Busiest Day</div>
                <div class="quick-stat-value"><?php echo $quickStats['busiest_day']; ?></div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-label">Avg. Revenue/Booking</div>
                <div class="quick-stat-value">₱<?php echo number_format($quickStats['avg_revenue_per_booking'], 2); ?></div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-label">Total Revenue Tracked</div>
                <div class="quick-stat-value">₱<?php echo number_format($quickStats['total_revenue'], 2); ?></div>
            </div>
        </div>

        <!-- Main Stats Summary -->
        <div class="stats-summary">
            <div class="stat-box">
                <div class="stat-icon stat-total"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <h4>Total Bookings</h4>
                    <div class="stat-number"><?php echo $totalBookings; ?></div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon stat-pending"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h4>Pending</h4>
                    <div class="stat-number"><?php echo $status_counts['pending']; ?></div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon stat-confirmed"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h4>Confirmed</h4>
                    <div class="stat-number"><?php echo $status_counts['confirmed']; ?></div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon stat-cancelled"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <h4>Cancelled</h4>
                    <div class="stat-number"><?php echo $status_counts['cancelled']; ?></div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon stat-growth"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <h4>Monthly Growth</h4>
                    <div class="stat-number"><?php echo $month_over_month_growth; ?>%</div>
                    <div class="stat-change <?php echo $month_over_month_growth >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $month_over_month_growth >= 0 ? 'up' : 'down'; ?>"></i>
                        vs previous month
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <div class="chart-card full-width">
                <h3><i class="fas fa-chart-line"></i> Monthly Booking Trends (<?php echo $current_year; ?>)</h3>
                <div class="chart-container-line"><canvas id="lineChart"></canvas></div>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Booking Status Distribution</h3>
                <div class="chart-container-pie"><canvas id="pieChart"></canvas></div>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-home"></i> Most Popular Cottages</h3>
                <div class="chart-container-bar"><canvas id="barChart"></canvas></div>
            </div>
        </div>

        <!-- Daily Activity Chart -->
        <div class="data-card-extended">
            <h3><i class="fas fa-calendar-week"></i> Daily Booking Activity (Last <?php echo $timeframe; ?> Days)</h3>
            <div class="chart-container-small">
                <canvas id="trendsChart"></canvas>
            </div>
        </div>

        <!-- Peak Dates Table -->
        <div class="data-card-extended">
            <h3><i class="fas fa-star"></i> Top 5 Peak Booking Dates</h3>
            <?php if (count($peakDates) > 0): ?>
                <table class="peak-dates-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Bookings</th>
                            <th>Total Revenue</th>
                            <th>Avg. per Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peakDates as $peak): ?>
                        <tr>
                            <td>
                                <i class="fas fa-calendar-day" style="color:#1e3c72; margin-right:8px;"></i> 
                                <?php echo date('F d, Y', strtotime($peak['date'])); ?><br>
                                <small style="color:#666;"><?php echo date('l', strtotime($peak['date'])); ?></small>
                            </td>
                            <td><strong><?php echo $peak['booking_count']; ?></strong> bookings</td>
                            <td class="revenue-highlight">₱<?php echo number_format($peak['total_revenue'], 2); ?></td>
                            <td>
                                ₱<?php echo number_format($peak['avg_per_booking'], 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 20px;">No peak date data available yet.</p>
            <?php endif; ?>
        </div>

    </main>

    <script>
        const colors = { 
            primary: '#1e3c72', 
            secondary: '#2a5298', 
            accent: '#00d2ff', 
            success: '#28a745', 
            warning: '#ffc107', 
            danger: '#e74c3c' 
        };
        
        // Show loading indicator
        function showLoading() {
            document.getElementById('loading').style.display = 'flex';
        }
        
        // Hide loading indicator
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }
        
        // Export to CSV function
        function exportToCSV() {
            showLoading();
            
            setTimeout(() => {
                // Create CSV content
                const csvContent = [
                    ['Analytics Report - <?php echo date("Y-m-d"); ?>'],
                    [''],
                    ['Metric', 'Value'],
                    ['Total Bookings', <?php echo $totalBookings; ?>],
                    ['Pending Bookings', <?php echo $status_counts['pending']; ?>],
                    ['Confirmed Bookings', <?php echo $status_counts['confirmed']; ?>],
                    ['Cancelled Bookings', <?php echo $status_counts['cancelled']; ?>],
                    ['Monthly Growth', '<?php echo $month_over_month_growth; ?>%'],
                    [''],
                    ['Peak Booking Dates'],
                    ['Date', 'Bookings', 'Revenue', 'Average per Booking']
                ];
                
                <?php foreach ($peakDates as $peak): ?>
                csvContent.push([
                    '<?php echo date("Y-m-d", strtotime($peak["date"])); ?>',
                    <?php echo $peak['booking_count']; ?>,
                    '₱<?php echo number_format($peak["total_revenue"], 2); ?>',
                    '₱<?php echo number_format($peak["avg_per_booking"], 2); ?>'
                ]);
                <?php endforeach; ?>
                
                // Convert to CSV string
                const csvString = csvContent.map(row => 
                    row.map(cell => `"${cell}"`).join(',')
                ).join('\n');
                
                // Create download link
                const blob = new Blob([csvString], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'analytics-report-<?php echo date("Y-m-d"); ?>.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                hideLoading();
            }, 500);
        }
        
        // 1. Line Chart - Monthly Trends
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{ 
                    label: 'Bookings', 
                    data: <?php echo json_encode(array_values($booking_counts)); ?>, 
                    borderColor: colors.accent, 
                    backgroundColor: 'rgba(0,210,255,0.1)', 
                    borderWidth: 3, 
                    pointBackgroundColor: '#fff',
                    pointBorderColor: colors.accent,
                    pointRadius: 5,
                    fill: true, 
                    tension: 0.4 
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } }, 
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [5, 5] } 
                    }, 
                    x: { 
                        grid: { display: false } 
                    } 
                } 
            }
        });

        // 2. Pie Chart - Status Distribution
        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Confirmed', 'Cancelled'],
                datasets: [{ 
                    data: [
                        <?php echo $status_counts['pending']; ?>, 
                        <?php echo $status_counts['confirmed']; ?>, 
                        <?php echo $status_counts['cancelled']; ?>
                    ], 
                    backgroundColor: [colors.warning, colors.success, colors.danger], 
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            padding: 20, 
                            usePointStyle: true 
                        } 
                    } 
                },
                cutout: '70%'
            }
        });

        // 3. Bar Chart - Popular Cottages
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($cottage_labels); ?>,
                datasets: [{ 
                    label: 'Bookings', 
                    data: <?php echo json_encode($cottage_data); ?>, 
                    backgroundColor: [
                        colors.primary, 
                        colors.secondary, 
                        colors.accent, 
                        '#4bc0c0', 
                        '#9966ff',
                        '#ff9f40',
                        '#ff6384',
                        '#c9cbcf'
                    ], 
                    borderRadius: 5 
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                indexAxis: 'y', 
                plugins: { legend: { display: false } }, 
                scales: { 
                    x: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1 } 
                    } 
                } 
            }
        });

        // 4. Trends Chart - Daily Activity
        const trendsCtx = document.getElementById('trendsChart');
        if (trendsCtx) {
            const formattedDates = <?php echo json_encode($weeklyDates); ?>.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });

            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: formattedDates,
                    datasets: [{
                        label: 'Daily Bookings',
                        data: <?php echo json_encode($weeklyBookings); ?>,
                        borderColor: colors.success,
                        backgroundColor: 'rgba(40, 167, 69, 0.05)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { stepSize: 1 } 
                        },
                        x: { 
                            grid: { display: false } 
                        }
                    },
                    plugins: { 
                        legend: { display: false } 
                    }
                }
            });
        }
        
        // Auto-hide loading indicator after page load
        window.addEventListener('load', function() {
            setTimeout(hideLoading, 500);
        });
        
        // Show loading when changing timeframe
        document.querySelectorAll('.timeframe-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!this.classList.contains('active')) {
                    showLoading();
                }
            });
        });
        
        // Auto-refresh every 5 minutes
        setTimeout(() => {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>