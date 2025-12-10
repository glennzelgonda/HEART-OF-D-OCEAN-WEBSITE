<?php
session_start();
$current_page = 'revenue';
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
    // Total revenue and confirmed bookings
    $revenueQuery = "SELECT 
        COALESCE(SUM(total_price), 0) as total_revenue,
        COUNT(*) as total_confirmed_bookings
        FROM bookings 
        WHERE status = 'confirmed' AND deleted = 0";
    $revenueStmt = $db->prepare($revenueQuery);
    $revenueStmt->execute();
    $revenueData = $revenueStmt->fetch(PDO::FETCH_ASSOC);
    
    $total_revenue = $revenueData['total_revenue'] ?? 0;
    $total_confirmed_bookings = $revenueData['total_confirmed_bookings'] ?? 0;
    
    // Monthly revenue data
    $monthlyRevenueQuery = "SELECT 
        MONTHNAME(date) as month,
        MONTH(date) as month_num,
        COALESCE(SUM(total_price), 0) as revenue,
        COUNT(*) as bookings_count
        FROM bookings 
        WHERE status = 'confirmed' 
        AND total_price IS NOT NULL
        AND YEAR(date) = YEAR(CURDATE())
        AND deleted = 0
        GROUP BY MONTH(date), MONTHNAME(date)
        ORDER BY month_num";
    $monthlyRevenueStmt = $db->prepare($monthlyRevenueQuery);
    $monthlyRevenueStmt->execute();
    $monthlyRevenueData = $monthlyRevenueStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Revenue by cottage types
    $revenueByCottageQuery = "SELECT 
        room,
        COALESCE(SUM(total_price), 0) as total_revenue,
        COUNT(*) as bookings_count
        FROM bookings 
        WHERE status = 'confirmed'
        AND total_price IS NOT NULL
        AND deleted = 0
        GROUP BY room 
        ORDER BY total_revenue DESC 
        LIMIT 10";
    $revenueByCottageStmt = $db->prepare($revenueByCottageQuery);
    $revenueByCottageStmt->execute();
    $revenueByCottage = $revenueByCottageStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) { 
    $total_revenue = 0;
    $total_confirmed_bookings = 0;
    $monthlyRevenueData = [];
    $revenueByCottage = [];
    error_log("Revenue data error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue - Admin</title>
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
        
        /* Stats Grid - Adjusted for fewer items */
        .stats-grid-revenue {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .revenue-card-detail {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .revenue-card-detail:hover { transform: translateY(-5px); }
        
        .revenue-hero-extended {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            padding: 2.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.3);
            position: relative;
            overflow: hidden;
        }
        .revenue-hero-extended::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none" opacity="0.1"><path d="M0,0 L100,0 L100,100 Z" fill="white"/></svg>');
            background-size: cover;
        }
        .revenue-hero-extended h1 { 
            font-size: 3rem; 
            margin: 0; 
            line-height: 1.2; 
            position: relative;
            z-index: 1;
        }
        .revenue-hero-extended p { 
            opacity: 0.9; 
            font-size: 1.1rem; 
            margin: 0 0 1rem 0; 
            position: relative;
            z-index: 1;
        }
        .revenue-icon { 
            font-size: 4rem; 
            opacity: 0.3; 
            position: relative;
            z-index: 1;
        }
        
        .revenue-peak-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .revenue-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .revenue-item:last-child {
            border-bottom: none;
        }
        .revenue-cottage {
            flex: 1;
            font-weight: 500;
            color: var(--dark);
        }
        .revenue-bookings {
            color: #666;
            font-size: 0.9rem;
            margin-right: 15px;
        }
        .revenue-amount {
            font-weight: 600;
            color: #27ae60;
        }
        
        .revenue-bar {
            height: 12px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            border-radius: 6px;
            margin-bottom: 15px;
            transition: width 1s ease;
        }
        
        .chart-label-revenue {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #555;
        }
        
        @media (max-width: 991px) {
            .revenue-peak-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-water"></i> Admin Panel</div>
        <ul class="menu-list">
            <li class="menu-item"><a href="dashboard.php" class="menu-link"><i class="fas fa-th-large"></i> Overview</a></li>
            <li class="menu-item"><a href="bookings.php" class="menu-link"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li class="menu-item"><a href="analytics.php" class="menu-link"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li class="menu-item"><a href="revenue.php" class="menu-link active"><i class="fas fa-coins"></i> Revenue</a></li>
            <li class="menu-item"><a href="availability.php" class="menu-link"><i class="fas fa-door-open"></i> Availability</a></li>
            <li class="menu-item" style="margin-top: 2rem;"><a href="logout.php" class="menu-link" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h2>Financial Overview</h2>
            <div class="user-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 600;">Admin</div>
                    <div style="font-size: 0.8rem; color: #777;">Administrator</div>
                </div>
                <div class="admin-avatar">A</div>
            </div>
        </div>

        <div class="last-update">
            Last updated: <?php echo date('Y-m-d H:i:s'); ?>
        </div>

        <div class="revenue-hero-extended">
            <div>
                <p>Total Revenue (Confirmed Bookings)</p>
                <h1>₱<?php echo number_format($total_revenue, 2); ?></h1>
                <p style="font-size: 0.9rem; margin-top: 10px;">
                    Based on <?php echo $total_confirmed_bookings; ?> confirmed bookings
                </p>
            </div>
            <div class="revenue-icon"><i class="fas fa-wallet"></i></div>
        </div>

        <div class="stats-grid-revenue">
            <div class="revenue-card-detail">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 10px; background: rgba(40, 167, 69, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #28a745;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div style="color: #6c757d; font-size: 0.9rem;">Confirmed Bookings</div>
                        <div style="font-size: 1.8rem; font-weight: 700;"><?php echo $total_confirmed_bookings; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="revenue-card-detail">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 10px; background: rgba(30, 60, 114, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #1e3c72;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div style="color: #6c757d; font-size: 0.9rem;">This Month</div>
                        <div style="font-size: 1.8rem; font-weight: 700;">
                            ₱<?php 
                                $monthRevenue = array_sum(array_column($monthlyRevenueData, 'revenue'));
                                echo number_format($monthRevenue, 2); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="revenue-peak-grid">
            <div class="data-card">
                <h3><i class="fas fa-chart-line"></i> Monthly Revenue (<?php echo date('Y'); ?>)</h3>
                <div style="padding: 1rem 0;">
                    <?php if (!empty($monthlyRevenueData)): ?>
                        <?php 
                        $maxRevenue = max(array_column($monthlyRevenueData, 'revenue'));
                        if ($maxRevenue == 0) $maxRevenue = 1;
                        ?>
                        <?php foreach ($monthlyRevenueData as $month): 
                            $width = ($month['revenue'] / $maxRevenue) * 100;
                        ?>
                        <div class="chart-label-revenue">
                            <span><?php echo $month['month']; ?></span>
                            <span>₱<?php echo number_format($month['revenue'], 2); ?></span>
                        </div>
                        <div class="revenue-bar" style="width: <?php echo $width; ?>%"></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding: 20px;">No revenue data available for this year</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="data-card">
                <h3><i class="fas fa-home"></i> Revenue by Cottage Type</h3>
                <div style="padding: 1rem 0;">
                    <?php if (!empty($revenueByCottage)): ?>
                        <?php foreach ($revenueByCottage as $cottage): ?>
                        <div class="revenue-item">
                            <div class="revenue-cottage"><?php echo htmlspecialchars($cottage['room']); ?></div>
                            <div class="revenue-bookings"><?php echo $cottage['bookings_count']; ?> bookings</div>
                            <div class="revenue-amount">₱<?php echo number_format($cottage['total_revenue'], 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding: 20px;">No revenue by cottage data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>
</body>
</html>