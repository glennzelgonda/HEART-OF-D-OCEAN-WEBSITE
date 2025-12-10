<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - Heart Of D' Ocean Beach Resort</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&family=Pacifico&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="logo" href="index.php">Heart Of D' Ocean Beach Resort</a>
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

  <main class="container">
    <div class="page-header">
      <h1>Contact Us</h1>
      <p>Get in touch with Heart Of D' Ocean Beach Resort</p>
    </div>

    <div class="contact-container">
      <div class="contact-info">
        <h2>We'd Love to Hear From You</h2>
        
        <div class="contact-item">
          <i class="fas fa-map-marker-alt"></i>
          <div>
            <h3>Address</h3>
            <p>Nonong Casto, Lemery, Batangas, Philippines</p>
          </div>
        </div>

        <div class="contact-item">
          <i class="fas fa-phone"></i>
          <div>
            <h3>Phone</h3>
            <p>0917 528 3832</p>
          </div>
        </div>

        <div class="contact-item">
          <i class="fas fa-envelope"></i>
          <div>
            <h3>Email</h3>
            <p>heartofdocean2005@yahoo.com</p>
          </div>
        </div>

        <div class="contact-item">
          <i class="fas fa-clock"></i>
          <div>
            <h3>Business Hours</h3>
            <p>Open 24/7</p>
          </div>
        </div>

        <div class="social-links">
          <h3>Follow Us</h3>
          <div class="social-icons">
            <a href="https://www.facebook.com/Heartofdoceanbeachresort/#" target="_blank">
              <i class="fab fa-facebook"></i>
            </a>
            <a href="https://www.facebook.com/messages/t/233219370026088" target="_blank">
              <i class="fab fa-facebook-messenger"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="contact-form">
        <h2>Send us a Message</h2>
        <form id="contactForm">
          <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" required>
          </div>

          <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" required>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone">
          </div>

          <div class="form-group">
            <label for="subject">Subject *</label>
            <select id="subject" name="subject" required>
              <option value="">Select a subject</option>
              <option value="booking">Booking Inquiry</option>
              <option value="rates">Rates Inquiry</option>
              <option value="events">Events & Parties</option>
              <option value="general">General Inquiry</option>
              <option value="complaint">Complaint</option>
              <option value="suggestion">Suggestion</option>
            </select>
          </div>

          <div class="form-group">
            <label for="message">Message *</label>
            <textarea id="message" name="message" rows="5" required></textarea>
          </div>

          <button type="submit" class="btn primary">Send Message</button>
        </form>
      </div>
    </div>

    <div class="map-container">
      <h2>Find Us</h2>
      <div class="map-placeholder">
        <a href="https://maps.app.goo.gl/q67iwWwZYtNH51rN8" target="_blank" class="map-link">
          <i class="fas fa-map-location-dot"></i>
          <span>View on Google Maps</span>
        </a>
      </div>
    </div>
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
          <li><a href="payment.php" class="footer-link">Payment Options</a></li>
          <li><a href="cancellation.php" class="footer-link">Cancellation Policy</a></li>
          <li><a href="terms.php" class="footer-link">Terms & Conditions</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Reach Us</h4>
        <div class="social-links">
          <a href="https://www.facebook.com/messages/t/233219370026088" target="_blank">
            <i class="fab fa-facebook-messenger"></i>
          </a>
          <a href="https://www.facebook.com/Heartofdoceanbeachresort/#" target="_blank">
            <i class="fab fa-facebook"></i>
          </a>
          <a href="mailto:heartofdocean2005@yahoo.com">
            <i class="fas fa-envelope"></i>
          </a>
          <a href="https://maps.app.goo.gl/q67iwWwZYtNH51rN8" target="_blank">
            <i class="fas fa-location-dot"></i>
          </a>
        </div>
        
        <!-- Contact Info -->
        <div class="contact-info">
          <p>📍 Nonong Casto, Lemery, Philippines</p>
          <p>📞 0917 528 3832</p>
          <p>⏰ Open 24/7</p>
        </div>
      </div>
    </div>
    
    <!-- Copyright -->
    <div class="footer-bottom">
      <p>&copy; 2025 Heart Of D' Ocean Beach Resort. All rights reserved.</p>
    </div>
  </div>
</footer>

  <script src="main.js"></script>
</body>
</html>