<?php
session_start();
include 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to check cottage availability
function checkCottageAvailability($db, $cottage, $checkin_date, $checkout_date) {
    try {
        // Check availability logic
        $query = "SELECT COUNT(*) as count FROM cottage_availability 
                  WHERE cottage_name = :cottage 
                  AND status = 'confirmed'
                  AND booked_date >= :checkin_date 
                  AND booked_date < :checkout_date";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":cottage", $cottage);
        $stmt->bindParam(":checkin_date", $checkin_date);
        $stmt->bindParam(":checkout_date", $checkout_date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    } catch(PDOException $e) {
        error_log("Availability check error: " . $e->getMessage());
        return false;
    }
}

// Function to calculate total price
function calculateTotalPrice($cottage, $nights) {
    $prices = [
        "white-house" => 30000,
        "penthouse" => 12800,
        "aqua-class" => 11800,
        "heartsuite" => 11800,
        "stephs-skylounge" => 11800,
        "stephs-848" => 10800,
        "stephs-846" => 10000,
        "concierge-817" => 9800,
        "de-luxe" => 8800,
        "concierge-815-819" => 8800,
        "premium-840" => 8800,
        "beatrice-a" => 7800,
        "premium-838" => 7800,
        "giant-kubo" => 6800,
        "seaside-whole" => 6800,
        "beatrice-b" => 6800,
        "seaside-half" => 3400,
        "bamboo-kubo" => 2800
    ];
    
    $pricePerNight = $prices[$cottage] ?? 0;
    return $pricePerNight * $nights;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission. Please refresh the page.";
    } else {
        // Sanitize input
        $name = trim($_POST['name']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $phone = trim($_POST['phone']);
        $accommodation = $_POST['accommodation'];
        $checkin_date = $_POST['checkin'];
        $checkout_date = $_POST['checkout'];
        $adults = intval($_POST['adults']);
        $children = intval($_POST['children'] ?? 0);
        $paymentMethod = $_POST['paymentMethod'] ?? '';
        $gcashName = trim($_POST['gcashName'] ?? '');
        $gcashNumber = trim($_POST['gcashNumber'] ?? '');
        $paymentReference = trim($_POST['paymentReference'] ?? '');
        $paymentDate = $_POST['paymentDate'] ?? '';
        
        $booking_id = 'RESORT' . time() . rand(100, 999);

        // Handle file upload
        $receiptFilename = '';
        $validUpload = true;

        if ($paymentMethod === 'pay-now') {
            if (isset($_FILES['receiptUpload']) && $_FILES['receiptUpload']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/receipts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileExtension = pathinfo($_FILES['receiptUpload']['name'], PATHINFO_EXTENSION);
                $receiptFilename = $booking_id . '_receipt.' . $fileExtension;
                $uploadPath = $uploadDir . $receiptFilename;
                
                // Validate file type
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                if (in_array(strtolower($fileExtension), $allowedTypes)) {
                    if (!move_uploaded_file($_FILES['receiptUpload']['tmp_name'], $uploadPath)) {
                        $validUpload = false;
                        $error = "Failed to upload receipt file. Please try again.";
                    }
                } else {
                    $validUpload = false;
                    $error = "Invalid file type. Please upload JPG, PNG, GIF, or PDF files only.";
                }
            } else {
                $validUpload = false;
                $error = "Please upload your payment receipt.";
            }
            
            // Validate GCash details
            if ($validUpload && (empty($gcashName) || empty($gcashNumber) || empty($paymentReference))) {
                $error = "Please fill in all GCash payment details.";
                $validUpload = false;
            }
        }

        // Validate fields
        if (empty($name) || !$email || empty($phone) || empty($accommodation) || empty($checkin_date) || empty($checkout_date)) {
            $error = "Please fill in all required fields.";
        } elseif ($validUpload) {
            // Calculate nights
            $checkin = new DateTime($checkin_date);
            $checkout = new DateTime($checkout_date);
            $nights = $checkin->diff($checkout)->days;
            
            if ($nights < 1) {
                $error = "Check-out date must be after check-in date.";
            } else {
                // Check availability
                if (!checkCottageAvailability($db, $accommodation, $checkin_date, $checkout_date)) {
                    $error = "Sorry, the selected cottage is not available for the selected dates. Please choose different dates.";
                } else {
                    try {
                        // Calculate total price
                        $total_price = calculateTotalPrice($accommodation, $nights);
                        
                        // Insert into database
                        $query = "INSERT INTO bookings (booking_id, name, email, phone, room, date, checkout_date, guests, children, nights, total_price, payment_method, gcash_name, gcash_number, payment_reference, payment_date, receipt_filename, status) 
                                  VALUES (:booking_id, :name, :email, :phone, :room, :date, :checkout_date, :guests, :children, :nights, :total_price, :payment_method, :gcash_name, :gcash_number, :payment_reference, :payment_date, :receipt_filename, 'pending')";
                        
                        $stmt = $db->prepare($query);
                        $stmt->execute([
                            ':booking_id' => $booking_id,
                            ':name' => $name,
                            ':email' => $email,
                            ':phone' => $phone,
                            ':room' => $accommodation,
                            ':date' => $checkin_date,
                            ':checkout_date' => $checkout_date,
                            ':guests' => $adults,
                            ':children' => $children,
                            ':nights' => $nights,
                            ':total_price' => $total_price,
                            ':payment_method' => $paymentMethod,
                            ':gcash_name' => $gcashName,
                            ':gcash_number' => $gcashNumber,
                            ':payment_reference' => $paymentReference,
                            ':payment_date' => $paymentDate,
                            ':receipt_filename' => $receiptFilename
                        ]);

                        if ($stmt->rowCount() > 0) {

                            $subject = "Booking Received - Heart Of D Ocean Beach Resort";
                           
                            $_SESSION['booking_id'] = $booking_id;
                            $_SESSION['nights'] = $nights;
                            $_SESSION['total_price'] = $total_price;
                            $_SESSION['booking_name'] = $name;
                            $_SESSION['booking_email'] = $email;
                            
                            header("Location: booking.php?success=1");
                            exit();
                        }
                    } catch(PDOException $exception) {
                        $error = "System error: " . $exception->getMessage();
                    }
                }
            }
        }
    }
}

// Display success message
$successMessage = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $booking_id = $_SESSION['booking_id'] ?? '';
    $nights = $_SESSION['nights'] ?? 1;
    $total_price = $_SESSION['total_price'] ?? 0;
    $name = $_SESSION['booking_name'] ?? '';
    $email = $_SESSION['booking_email'] ?? '';
    
$successMessage = "
    <div class='booking-success'>
        <div class='success-header-bg'>
            <div class='success-checkmark'>
                <i class='fas fa-check'></i>
            </div>
            <h2>Booking Confirmed</h2>
            <p class='success-subtitle'>Your reservation has been secured.</p>
        </div>
        
        <div class='booking-details'>
            <div class='detail-row'>
                <span class='detail-label'><i class='fas fa-hashtag'></i> Reference No.</span>
                <span class='detail-value highlight'>$booking_id</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'><i class='fas fa-user'></i> Guest Name</span>
                <span class='detail-value'>$name</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'><i class='fas fa-calendar'></i> Duration</span>
                <span class='detail-value'>$nights night" . ($nights > 1 ? 's' : '') . "</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'><i class='fas fa-envelope'></i> Email Sent To</span>
                <span class='detail-value'>$email</span>
            </div>

            <div class='detail-row' style='margin-top: 10px; border-top: 2px dashed #eee; padding-top: 15px;'>
                <span class='detail-label'><i class='fas fa-money-bill-wave'></i> Total Amount</span>
                <span class='detail-value total-price'>₱" . number_format($total_price, 2) . "</span>
            </div>
        </div>
        
        <div class='confirmation-note'>
            <i class='fas fa-info-circle'></i>
            <span>We sent a receipt to your email. Please check your spam folder if not received.</span>
        </div>
        
        <div class='action-buttons'>
            <a href='index.php' class='btn-home' style='text-decoration:none;'>Return to Home</a>
        </div>
    </div>
";
    unset($_SESSION['booking_id'], $_SESSION['nights'], $_SESSION['total_price'], $_SESSION['booking_name'], $_SESSION['booking_email']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking - Heart Of D' Ocean Beach Resort</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="logo" href="index.php">
         <img src="images&vids/logo1.png" alt="Heart Of D' Ocean Logo">
      </a>
      <nav class="nav" id="mainNav">
        <button class="close-menu" id="closeMenu">✕</button>
        <a href="index.php">Home</a>
        <a href="rooms.php">Cottages</a>
        <a href="gallery.php">Gallery</a>
        <a href="booking.php" class="cta">Book Now</a>
        <button id="darkToggle" class="icon-btn" aria-label="Toggle dark mode">🌙</button>
      </nav>
      <button id="menuBtn" class="hamburger" aria-label="Toggle menu">☰</button>
    </div>
  </header>

  <main class="container booking-page">
    
    <?php if ($successMessage): ?>
        <div class="success-container">
            <?php echo $successMessage; ?>
        </div>
        
    <?php else: ?> 
        <?php 
            // Date Logic for Placeholder
            $minDate = date('Y-m-d');
            $maxDate = '2026-12-31';
            
            
            $checkinValue = isset($_POST['checkin']) ? $_POST['checkin'] : '';
            $checkinType = $checkinValue ? 'date' : 'text';
            
            $checkoutValue = isset($_POST['checkout']) ? $_POST['checkout'] : '';
            $checkoutType = $checkoutValue ? 'date' : 'text';
        ?>

        <div class="booking-header">
            <h1>Make a Reservation</h1>
            <p>Plan your perfect getaway with us</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form id="bookingForm" method="POST" action="booking.php" class="booking-form" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          
          <div class="form-section">
              <h3><i class="fas fa-calendar-alt"></i> Select Dates</h3>
              <div class="form-row">
                <div class="form-group">
                  <label for="checkin">Check-in Date *</label>
                  <div class="input-wrapper">
                      <input type="<?php echo $checkinType; ?>" id="checkin" name="checkin" 
                             placeholder="mm/dd/yyyy"
                             onfocus="(this.type='date')" 
                             onblur="(this.value ? this.type='date' : this.type='text')"
                             min="<?php echo $minDate; ?>" 
                             max="<?php echo $maxDate; ?>" 
                             value="<?php echo $checkinValue; ?>" 
                             required>
                      <i class="fas fa-calendar-day input-icon"></i>
                  </div>
                </div>
                <div class="form-group">
                  <label for="checkout">Check-out Date *</label>
                  <div class="input-wrapper">
                      <input type="<?php echo $checkoutType; ?>" id="checkout" name="checkout" 
                             placeholder="mm/dd/yyyy"
                             onfocus="(this.type='date')" 
                             onblur="(this.value ? this.type='date' : this.type='text')"
                             min="<?php echo $minDate; ?>" 
                             max="<?php echo $maxDate; ?>" 
                             value="<?php echo $checkoutValue; ?>" 
                             required>
                      <i class="fas fa-calendar-check input-icon"></i>
                  </div>
                </div>
              </div>
          </div>

          <div class="form-section">
            <h3><i class="fas fa-home"></i> Accommodation</h3>
            <div class="form-group">
                <label for="accommodation">Choose Cottage *</label>
                <div class="input-wrapper">
                    <select id="accommodation" name="accommodation" required onchange="updateBookButton()">
                      <option value="">Select accommodation...</option>
                      <optgroup label="Premium Cottages">
                        <option value="white-house" data-price="30000">White House - ₱30,000/night</option>
                        <option value="penthouse" data-price="12800">Penthouse - ₱12,800/night</option>
                        <option value="aqua-class" data-price="11800">Aqua Class - ₱11,800/night</option>
                        <option value="heartsuite" data-price="11800">Heartsuite - ₱11,800/night</option>
                        <option value="stephs-skylounge" data-price="11800">Steph's Skylounge - ₱11,800/night</option>
                      </optgroup>
                      <optgroup label="Standard Cottages">
                        <option value="stephs-848" data-price="10800">Steph's 848 - ₱10,800/night</option>
                        <option value="stephs-846" data-price="10000">Steph's 846 - ₱10,000/night</option>
                        <option value="concierge-817" data-price="9800">Concierge 817 - ₱9,800/night</option>
                        <option value="de-luxe" data-price="8800">De Luxe - ₱8,800/night</option>
                        <option value="concierge-815-819" data-price="8800">Concierge 815/819 - ₱8,800/night</option>
                        <option value="premium-840" data-price="8800">Premium 840 - ₱8,800/night</option>
                        <option value="beatrice-a" data-price="7800">Beatrice A - ₱7,800/night</option>
                        <option value="premium-838" data-price="7800">Premium 838 - ₱7,800/night</option>
                        <option value="giant-kubo" data-price="6800">Giant Kubo - ₱6,800/day</option>
                        <option value="seaside-whole" data-price="6800">Seaside (Whole) - ₱6,800/day</option>
                        <option value="beatrice-b" data-price="6800">Beatrice B - ₱6,800/night</option>
                      </optgroup>
                      <optgroup label="Budget Cottages">
                        <option value="seaside-half" data-price="3400">Seaside (Half) - ₱3,400/day</option>
                        <option value="bamboo-kubo" data-price="2800">Bamboo Kubo - ₱2,800/day</option>
                      </optgroup>
                    </select>
                    <i class="fas fa-bed input-icon"></i>
                </div>
            </div>
            
            <div id="bookingDisabledMessage" class="booking-disabled-message" style="display: none;">
                <i class="fas fa-exclamation-circle"></i> This cottage is already booked for your selected dates.
            </div>
          </div>

          <div class="form-section">
             <h3><i class="fas fa-user-group"></i> Guest Details</h3>
             <div class="form-row">
                <div class="form-group">
                  <label for="name">Full Name *</label>
                  <div class="input-wrapper">
                      <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required placeholder="Enter full name">
                      <i class="fas fa-user input-icon"></i>
                  </div>
                </div>
             </div>
             <div class="form-row">
                <div class="form-group">
                  <label for="email">Email Address *</label>
                  <div class="input-wrapper">
                      <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required placeholder="name@example.com">
                      <i class="fas fa-envelope input-icon"></i>
                  </div>
                </div>
                <div class="form-group">
                  <label for="phone">Phone Number *</label>
                  <div class="input-wrapper">
                      <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required placeholder="0917 123 4567">
                      <i class="fas fa-phone input-icon"></i>
                  </div>
                </div>
             </div>
             <div class="form-row">
                <div class="form-group">
                  <label for="adults">Adults *</label>
                  <div class="input-wrapper">
                      <input type="number" id="adults" name="adults" min="1" max="50" value="<?php echo isset($_POST['adults']) ? $_POST['adults'] : '2'; ?>" required>
                      <i class="fas fa-users input-icon"></i>
                  </div>
                </div>
                <div class="form-group">
                  <label for="children">Children</label>
                  <div class="input-wrapper">
                      <input type="number" id="children" name="children" min="0" max="20" value="<?php echo isset($_POST['children']) ? $_POST['children'] : '0'; ?>">
                      <i class="fas fa-child input-icon"></i>
                  </div>
                </div>
             </div>
          </div>

          <div class="form-section">
            <h3><i class="fas fa-credit-card"></i> Payment</h3>
            <div class="form-group">
                <label for="paymentMethod">Payment Method *</label>
                <div class="input-wrapper">
                    <select id="paymentMethod" name="paymentMethod" required onchange="togglePaymentOption()">
                      <option value="">Select payment method...</option>
                      <option value="pay-now" <?php echo (isset($_POST['paymentMethod']) && $_POST['paymentMethod'] == 'pay-now') ? 'selected' : ''; ?>>Pay Now (GCash)</option>
                    </select>
                    <i class="fas fa-wallet input-icon"></i>
                </div>
            </div>

            <div id="qrSection" class="qr-section" style="display: none;">
                <div class="gcash-card">
                    <div class="gcash-header">
                        <img src="images&vids/qrcode.jpg" alt="GCash QR Code" class="qr-img">
                        <div class="qr-info">
                            <h4>Scan to Pay</h4>
                            <p>Send exactly: <strong id="qrAmount" class="amount-highlight">₱0</strong></p>
                            <p class="ref-note">Ref No: <span id="qrReference">RESORT...</span></p>
                        </div>
                    </div>
                    
                    <div class="upload-area">
                      <label>Upload Payment Screenshot *</label>
                      <input type="file" id="receiptUpload" name="receiptUpload" accept="image/*,.pdf" class="file-input">
                    </div>

                    <div class="payment-fields">
                        <div class="form-group">
                            <input type="text" id="gcashName" name="gcashName" placeholder="GCash Account Name" class="simple-input" value="<?php echo isset($_POST['gcashName']) ? htmlspecialchars($_POST['gcashName']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <input type="tel" id="gcashNumber" name="gcashNumber" placeholder="GCash Mobile Number" class="simple-input" value="<?php echo isset($_POST['gcashNumber']) ? htmlspecialchars($_POST['gcashNumber']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" id="paymentReference" name="paymentReference" placeholder="Reference Number" class="simple-input" value="<?php echo isset($_POST['paymentReference']) ? htmlspecialchars($_POST['paymentReference']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <input type="date" id="paymentDate" name="paymentDate" class="simple-input" value="<?php echo isset($_POST['paymentDate']) ? htmlspecialchars($_POST['paymentDate']) : ''; ?>">
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <div class="price-breakdown" id="priceBreakdown" style="display:none;">
            <h3>Price Breakdown</h3>
            <div class="breakdown-row">
              <span>Cottage Rate</span>
              <span id="accommodationPrice">₱0</span>
            </div>
            <div class="breakdown-row">
              <span>Duration</span>
              <span id="nightsCount">0</span>
            </div>
            <div class="breakdown-total">
              <span>Total Amount</span>
              <span id="totalAmount">₱0</span>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" id="bookNowButton" class="btn-submit" disabled>Book Now</button>
            <button type="button" id="resetForm" class="btn-reset">Reset Form</button>
          </div>
        </form>

    <?php endif; ?>
    
  </main>

  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="footer-col">
          <h4>Company</h4>
          <ul>
            <li><a href="about.php" class="footer-link">About Us</a></li>
            <li><a href="contact.php" class="footer-link">Contact</a></li>
          </ul>
        </div>
        
        <div class="footer-col">
          <h4>Help</h4>
          <ul>
            <li><a href="faq.php" class="footer-link">FAQ</a></li>
            <li><a href="faq.php#payment" class="footer-link">Payment Options</a></li>
            <li><a href="faq.php#cancellation" class="footer-link">Cancellation Policy</a></li>
            <li><a href="faq.php#terms" class="footer-link">Terms & Conditions</a></li>
          </ul>
        </div>
        
        <div class="footer-col">
          <h4>Reach Us</h4>
          <div class="social-links">
            <a href="https://www.facebook.com/messages/t/233219370026088" target="_blank"><i class="fab fa-facebook-messenger"></i></a>
            <a href="https://www.facebook.com/Heartofdoceanbeachresort/#" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="mailto:heartofdocean2005@yahoo.com"><i class="fas fa-envelope"></i></a>
            <a href="https://maps.app.goo.gl/q67iwWwZYtNH51rN8" target="_blank"><i class="fas fa-location-dot"></i></a>
          </div>
          
          <div class="contact-info">
            <p>📍 Nonong Casto, Lemery, Batangas</p>
            <p>📞 0917 528 3832</p>
            <p>⏰ Open 24/7</p>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>© 2025 Heart Of D' Ocean Beach Resort. All rights reserved.</p>
      </div>
    </div>
  </footer>
  <script src="main.js"></script>
</body>
</html>