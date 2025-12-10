<?php
session_start();
date_default_timezone_set('Asia/Manila');
$current_page = 'availability';
include '../config.php'; 

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Get Month and Year
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Get filter if set
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Create date object
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$dateComponents = getdate($firstDayOfMonth);
$monthName = $dateComponents['month'];
$dayOfWeek = $dateComponents['wday'];

// Cottage List with Categories (DISPLAY NAMES ONLY - NO PRICES)
$cottages = [
    "White House",
    "Penthouse", 
    "Aqua Class",
    "Heartsuite",
    "Steph's Skylounge 842/844",
    "Steph's 848",
    "Steph's 846",
    "Concierge 817",
    "De Luxe",
    "Concierge 815/819",
    "Premium 840",
    "Beatrice A",
    "Premium 838",
    "Beatrice B",
    "Giant Kubo",
    "Seaside (Whole)",
    "Seaside (Half)",
    "Bamboo Kubo"
];
$totalCottages = count($cottages);

// Cottage Categories for Filtering
$categories = [
    'all' => 'All Cottages',
    'premium' => 'Premium Villas',
    'stephs' => 'Steph\'s Rooms',
    'concierge' => 'Concierge Rooms',
    'beatrice' => 'Beatrice Rooms',
    'kubos' => 'Kubos',
    'seaside' => 'Seaside'
];

// Cottage Mapping to Categories
$cottageCategories = [
    'premium' => ['White House', 'Penthouse', 'Aqua Class', 'Heartsuite'],
    'stephs' => ["Steph's Skylounge 842/844", "Steph's 848", "Steph's 846"],
    'concierge' => ['Concierge 817', 'De Luxe', 'Concierge 815/819'],
    'beatrice' => ['Premium 840', 'Beatrice A', 'Premium 838', 'Beatrice B'],
    'kubos' => ['Giant Kubo', 'Bamboo Kubo'],
    'seaside' => ['Seaside (Whole)', 'Seaside (Half)']
];

// Filter cottages based on selection
$filteredCottages = $cottages;
if ($filter !== 'all' && isset($cottageCategories[$filter])) {
    $filteredCottages = $cottageCategories[$filter];
}
$displayCottagesCount = count($filteredCottages);

// Function to normalize cottage names from database - FIXED VERSION
function normalizeCottageFromDB($cottageName) {
    $cottageName = trim($cottageName);
    
    // Remove price from cottage name (if present)
    if (strpos($cottageName, '—') !== false) {
        $parts = explode('—', $cottageName);
        $cottageName = trim($parts[0]);
    }
    
    // Also try dash with price
    if (strpos($cottageName, '- ₱') !== false) {
        $parts = explode('- ₱', $cottageName);
        $cottageName = trim($parts[0]);
    }
    
    // Remove any currency symbols and numbers at the end
    $cottageName = preg_replace('/\s*[—-]\s*₱\s*\d+[,\d\.]*/', '', $cottageName);
    $cottageName = preg_replace('/- ₱\d+[,\d\.]*/', '', $cottageName);
    
    // Map slug names to display names WITHOUT PRICE
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
    
    $lowerName = strtolower($cottageName);
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
    
    return trim($cottageName);
}

// Fetch Bookings - FIXED QUERY
try {
    $startDate = "$year-$month-01";
    $endDate = "$year-$month-$daysInMonth";
    
    $sql = "SELECT 
                ca.booked_date,
                ca.cottage_name,
                b.name as guest_name,
                b.booking_id,
                ca.status
            FROM cottage_availability ca
            LEFT JOIN bookings b ON ca.booking_id = b.booking_id
            WHERE ca.booked_date BETWEEN :start AND :end
            AND ca.status = 'confirmed'
            AND (b.deleted = 0 OR b.deleted IS NULL OR b.booking_id IS NULL)
            ORDER BY ca.booked_date";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([':start' => $startDate, ':end' => $endDate]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process and normalize cottage names
    $calendarData = [];
    foreach($bookings as $b) {
        $date = $b['booked_date'];
        $normalizedCottage = normalizeCottageFromDB($b['cottage_name']);
        
        if(!isset($calendarData[$date])) {
            $calendarData[$date] = [];
        }
        
        // Store with normalized name
        $b['cottage_name_normalized'] = $normalizedCottage;
        $calendarData[$date][] = $b;
    }
    
} catch (Exception $e) {
    error_log("Calendar error: " . $e->getMessage());
    $calendarData = [];
    $bookings = [];
}

// Navigation
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Availability Calendar - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        /* Calendar Styles */
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .month-nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .month-nav-btn {
            background: #f0f2f5;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            color: #1e3c72;
            font-size: 1.2rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .month-nav-btn:hover { background: #1e3c72; color: white; }
        
        .calendar-title {
            margin: 0;
            color: #1e3c72;
            font-size: 1.5rem;
            min-width: 200px;
            text-align: center;
        }
        
        /* Today Button */
        .today-btn {
            padding: 10px 20px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .today-btn:hover {
            background: #2a5298;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 60, 114, 0.3);
        }
        
        /* Date Search */
        .date-search {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .date-search input {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            min-width: 150px;
            transition: border 0.3s;
        }
        
        .date-search input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 2px rgba(30, 60, 114, 0.1);
        }
        
        .date-search button {
            padding: 10px 15px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .date-search button:hover {
            background: #2a5298;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .filter-header h3 {
            margin: 0;
            color: #1e3c72;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-tag {
            padding: 8px 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            color: #666;
            text-decoration: none;
        }
        
        .filter-tag:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .filter-tag.active {
            background: #1e3c72;
            color: white;
            border-color: #1e3c72;
            font-weight: 600;
        }
        
        .filter-stats {
            margin-top: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .filter-stats strong {
            color: #1e3c72;
        }
        
        /* Calendar Grid */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .day-name {
            text-align: center;
            font-weight: 600;
            color: #666;
            padding: 10px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        .calendar-day {
            min-height: 120px;
            background: #f8fbff;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 10px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .calendar-day:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #1e3c72;
        }
        
        /* PAST DAY STYLING (Grayed out) */
        .calendar-day.past-day {
            background: #f4f4f4;
            opacity: 0.7;
            border-color: #eee;
        }
        .calendar-day.past-day:hover {
            transform: none;
            box-shadow: none;
            cursor: default;
        }
        
        .day-number {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }
        
        .today .day-number {
            background: #1e3c72;
            color: white;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        /* Status Bar Colors */
        .status-bar {
            margin-top: auto;
            text-align: center;
            padding: 6px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-available { background: #d4edda; color: #155724; }
        .status-partial { background: #fff3cd; color: #856404; }
        .status-full { background: #f8d7da; color: #721c24; }
        .status-past { background: #e9ecef; color: #adb5bd; }
        
        .empty-day { background: transparent; border: none; cursor: default; }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
        }
        
        .modal-header {
            background: #1e3c72;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .booking-item {
            padding: 12px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .calendar-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .month-nav {
                justify-content: center;
            }
            
            .date-search {
                justify-content: center;
            }
            
            .calendar-grid { 
                grid-template-columns: repeat(1, 1fr); 
                gap: 15px; 
            }
            
            .calendar-day { 
                min-height: 80px; 
                flex-direction: row; 
                align-items: center; 
            }
            
            .status-bar { 
                margin-top: 0; 
                margin-left: auto;
            }
            
            .filter-tags {
                justify-content: center;
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
            <li class="menu-item"><a href="revenue.php" class="menu-link"><i class="fas fa-coins"></i> Revenue</a></li>
            <li class="menu-item"><a href="availability.php" class="menu-link active"><i class="fas fa-door-open"></i> Availability</a></li>
            <li class="menu-item" style="margin-top: 2rem;"><a href="logout.php" class="menu-link" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h2>Availability Calendar</h2>
            <div class="user-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 600;">Admin</div>
                    <div style="font-size: 0.8rem; color: #777;">Administrator</div>
                </div>
                <div class="admin-avatar">A</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-header">
                <h3><i class="fas fa-filter"></i> Filter by Cottage Type</h3>
            </div>
            <div class="filter-tags">
                <?php foreach ($categories as $key => $label): ?>
                    <a href="?month=<?php echo $month; ?>&year=<?php echo $year; ?>&filter=<?php echo $key; ?>" 
                       class="filter-tag <?php echo $filter == $key ? 'active' : ''; ?>">
                        <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="filter-stats">
                Currently showing: <strong><?php echo $displayCottagesCount; ?> cottages</strong> 
                <?php if($filter !== 'all'): ?>
                    (Filtered from <?php echo $totalCottages; ?> total cottages)
                <?php endif; ?>
            </div>
        </div>

        <div class="calendar-header">
            <div class="month-nav">
                <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>&filter=<?php echo $filter; ?>" class="month-nav-btn">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <h2 class="calendar-title"><?php echo "$monthName $year"; ?></h2>
                <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>&filter=<?php echo $filter; ?>" class="month-nav-btn">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>

            <!-- Today Button -->
            <button class="today-btn" onclick="goToToday()">
                <i class="fas fa-calendar-day"></i> Today
            </button>

            <!-- Date Search -->
            <div class="date-search">
                <input type="date" id="dateJump" value="<?php echo date('Y-m-d'); ?>">
                <button onclick="jumpToDate()">Go to Date</button>
            </div>
        </div>

        <div class="calendar-grid">
            <div class="day-name">Sun</div>
            <div class="day-name">Mon</div>
            <div class="day-name">Tue</div>
            <div class="day-name">Wed</div>
            <div class="day-name">Thu</div>
            <div class="day-name">Fri</div>
            <div class="day-name">Sat</div>

            <?php
            // Empty slots
            for ($i = 0; $i < $dayOfWeek; $i++) {
                echo '<div class="empty-day"></div>';
            }

            // Days loop
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $todayDate = date('Y-m-d');
                
                $isToday = ($currentDate == $todayDate);
                $isPast = ($currentDate < $todayDate);
                
                // Calculate bookings for filtered cottages - FIXED LOGIC
                $dayBookings = $calendarData[$currentDate] ?? [];
                $filteredBookings = [];
                
                foreach ($dayBookings as $booking) {
                    $normalizedCottage = normalizeCottageFromDB($booking['cottage_name']);
                    if (in_array($normalizedCottage, $filteredCottages)) {
                        $filteredBookings[] = [
                            'cottage_name' => $normalizedCottage,
                            'guest_name' => $booking['guest_name'],
                            'booking_id' => $booking['booking_id']
                        ];
                    }
                }
                
                $bookedCount = count($filteredBookings);
                $availableCount = $displayCottagesCount - $bookedCount;
                
                // Logic for Status
                $dayClass = '';
                $clickAction = '';
                
                if ($isPast) {
                    // PAST DATES
                    $statusClass = 'status-past';
                    $statusText = 'Ended';
                    $dayClass = 'past-day';
                    $clickAction = ""; 
                } elseif ($bookedCount >= $displayCottagesCount) {
                    // FULL BOOKED
                    $statusClass = 'status-full';
                    $statusText = 'Full Booked';
                    $modalData = htmlspecialchars(json_encode($filteredBookings), ENT_QUOTES, 'UTF-8');
                    $clickAction = "onclick='openDayModal(\"$currentDate\", $modalData)'";
                } elseif ($bookedCount > 0) {
                    // PARTIAL
                    $statusClass = 'status-partial';
                    $statusText = "$availableCount Left";
                    $modalData = htmlspecialchars(json_encode($filteredBookings), ENT_QUOTES, 'UTF-8');
                    $clickAction = "onclick='openDayModal(\"$currentDate\", $modalData)'";
                } else {
                    // AVAILABLE
                    $statusClass = 'status-available';
                    $statusText = 'Available';
                    $modalData = htmlspecialchars(json_encode([]), ENT_QUOTES, 'UTF-8');
                    $clickAction = "onclick='openDayModal(\"$currentDate\", $modalData)'";
                }

                echo "
                <div class='calendar-day " . ($isToday ? "today" : "") . " $dayClass' $clickAction>
                    <div class='day-number'>$day</div>
                    <div class='status-bar $statusClass'>
                        $statusText
                    </div>
                </div>";
            }
            ?>
        </div>
    </main>

    <div id="dayModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin:0;"><i class="fas fa-calendar-day"></i> Bookings for <span id="modalDate"></span></h3>
                <button onclick="closeModal()" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <div class="modal-body" id="modalList" style="padding: 0; max-height: 400px; overflow-y: auto;">
                </div>
            <div style="padding: 15px; text-align: center; background: #f8f9fa;">
                <button onclick="closeModal()" style="padding: 8px 20px; background: #1e3c72; color: white; border: none; border-radius: 5px; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Open Day Modal
        function openDayModal(date, bookings) {
            const dateObj = new Date(date);
            document.getElementById('modalDate').innerText = dateObj.toLocaleDateString('en-US', { 
                month: 'long', 
                day: 'numeric', 
                year: 'numeric' 
            });
            
            const listContainer = document.getElementById('modalList');
            listContainer.innerHTML = '';

            if (bookings.length === 0) {
                listContainer.innerHTML = `
                    <div style="text-align:center; padding: 40px; color: #888;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745; margin-bottom: 10px;"></i>
                        <p>No bookings for this date.</p>
                        <p><strong>All <?php echo $displayCottagesCount; ?> cottages are available.</strong></p>
                    </div>`;
            } else {
                bookings.forEach(booking => {
                    const item = document.createElement('div');
                    item.className = 'booking-item';
                    item.innerHTML = `
                        <div>
                            <div style="font-weight:600; color:#1e3c72;">${booking.cottage_name}</div>
                            <div style="font-size:0.9rem; color:#555;">Guest: ${booking.guest_name || 'Not specified'}</div>
                        </div>
                        <a href="bookings.php?search=${booking.booking_id}" 
                           style="color: #1e3c72; text-decoration: none; font-size: 0.85rem; border: 1px solid #ddd; padding: 4px 8px; border-radius: 4px; transition: all 0.3s;"
                           onmouseover="this.style.background='#1e3c72'; this.style.color='white';"
                           onmouseout="this.style.background=''; this.style.color='#1e3c72';">
                            View <i class="fas fa-arrow-right"></i>
                        </a>
                    `;
                    listContainer.appendChild(item);
                });
            }

            document.getElementById('dayModal').style.display = 'flex';
        }

        // Close Modal
        function closeModal() {
            document.getElementById('dayModal').style.display = 'none';
        }

        // Go to Today
        function goToToday() {
            const today = new Date();
            const month = today.getMonth() + 1;
            const year = today.getFullYear();
            window.location.href = `?month=${month}&year=${year}&filter=<?php echo $filter; ?>`;
        }

        // Jump to Date
        function jumpToDate() {
            const dateInput = document.getElementById('dateJump').value;
            if (!dateInput) {
                alert('Please select a date first.');
                return;
            }
            
            const date = new Date(dateInput);
            const month = date.getMonth() + 1;
            const year = date.getFullYear();
            window.location.href = `?month=${month}&year=${year}&filter=<?php echo $filter; ?>`;
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.id === 'dateJump' && e.key === 'Enter') {
                jumpToDate();
            }
            
            if ((e.ctrlKey || e.metaKey) && e.key === 't') {
                e.preventDefault();
                goToToday();
            }
            
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Set max date for date picker (next year)
        window.onload = function() {
            const today = new Date();
            const nextYear = new Date();
            nextYear.setFullYear(today.getFullYear() + 1);
            
            const dateInput = document.getElementById('dateJump');
            dateInput.min = today.toISOString().split('T')[0];
            dateInput.max = nextYear.toISOString().split('T')[0];
            
            // Close modal on outside click
            window.onclick = function(e) {
                if (e.target == document.getElementById('dayModal')) {
                    closeModal();
                }
            }
        };
    </script>
</body>
</html>