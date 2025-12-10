<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Cottages — Heart Of D' Ocean Beach Resort</title>

  <<link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&family=Pacifico&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        <a href="rooms.php" class="active">Cottages</a>
        <a href="gallery.php">Gallery</a>
        <a href="booking.php" class="cta">Book Now</a>
        <button id="darkToggle" class="icon-btn" aria-label="Toggle dark mode">🌙</button>
      </nav>
      <button id="menuBtn" class="hamburger" aria-label="Toggle menu">☰</button>
    </div>
  </header>

  <main class="container">
    <h1 class="rooms-title">Cottages & Rates</h1>
    <div class="cards">
      <!-- Cottage cards with HTML images but PHP format -->
      <div class="room-card image-modal-trigger" data-cottage="WHITE HOUSE">
        <img src="images&vids/WhiteHouse.png" alt="White House">
        <div class="room-body">
          <h3>WHITE HOUSE</h3>
          <p class="muted">₱30,000 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="PENTHOUSE">
        <img src="images&vids/PentHouse.jpg" alt="Penthouse">
        <div class="room-body">
          <h3>PENTHOUSE</h3>
          <p class="muted">₱12,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="AQUA CLASS">
        <img src="images&vids/Aqua.jpg" alt="Aqua Class">
        <div class="room-body">
          <h3>AQUA CLASS</h3>
          <p class="muted">₱11,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="HEARTSUITE">
        <img src="images&vids/HeartSuite.jpg" alt="Heartsuite">
        <div class="room-body">
          <h3>HEARTSUITE</h3>
          <p class="muted">₱11,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="STEPH'S SKYLOUNGE 842/844">
        <img src="images&vids/SkyLounge.png" alt="Steph's Skylounge">
        <div class="room-body">
          <h3>STEPH'S SKYLOUNGE 842/844</h3>
          <p class="muted">₱11,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="DE LUXE">
        <img src="images&vids/Deluxe.jpg" alt="De Luxe">
        <div class="room-body">
          <h3>DE LUXE</h3>
          <p class="muted">₱8,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="BEATRICE A">
        <img src="images&vids/BeatriceA.jpg" alt="Beatrice A">
        <div class="room-body">
          <h3>BEATRICE A</h3>
          <p class="muted">₱7,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="BEATRICE B">
        <img src="images&vids/BeatriceB.jpg" alt="Beatrice B">
        <div class="room-body">
          <h3>BEATRICE B</h3>
          <p class="muted">₱6,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="CONCIERGE 815/819">
        <img src="images&vids/Concierge.jpg" alt="Concierge">
        <div class="room-body">
          <h3>CONCIERGE 815/819</h3>
          <p class="muted">₱8,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="PREMIUM 838">
        <img src="images&vids/Premium838.jpg" alt="Premium">
        <div class="room-body">
          <h3>PREMIUM 838</h3>
          <p class="muted">₱7,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="PREMIUM 840">
        <img src="images&vids/Premium840.jpg" alt="Premium 840">
        <div class="room-body">
          <h3>PREMIUM 840</h3>
          <p class="muted">₱8,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="GIANT KUBO">
        <img src="images&vids/GiantKubo.jpg" alt="Giant Kubo">
        <div class="room-body">
          <h3>GIANT KUBO</h3>
          <p class="muted">₱6,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="SEASIDE (WHOLE)">
        <img src="images&vids/SeaSide.jpg" alt="Seaside Whole">
        <div class="room-body">
          <h3>SEASIDE (WHOLE)</h3>
          <p class="muted">₱6,800 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="SEASIDE (HALF)">
        <img src="images&vids/SeaSide.jpg" alt="Seaside Half">
        <div class="room-body">
          <h3>SEASIDE (HALF)</h3>
          <p class="muted">₱3,400 per night</p>
        </div>
      </div>

      <div class="room-card image-modal-trigger" data-cottage="BAMBOO KUBO">
        <img src="images&vids/BambooKubo.jpg" alt="Bamboo Kubo">
        <div class="room-body">
          <h3>BAMBOO KUBO</h3>
          <p class="muted">₱2,800 per night</p>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal Structure -->
  <div class="modal" id="cottageModal">
    <div class="modal-content">
      <button class="close-modal">&times;</button>
      <div class="modal-body">
        <div class="modal-image-container">
          <img class="modal-image" src="" alt="">
        </div>
        
        <!-- Horizontal Gallery Section for More Photos -->
        <div class="modal-gallery">
          <div class="gallery-title">More Photos</div>
          <div class="gallery-scroll" id="galleryScroll">
          </div>
        </div>
        
        <div class="modal-info">
          <h2 class="modal-title"></h2>
          <p class="modal-description"></p>
          <div class="modal-details">
            <div class="detail-item">
              <span class="detail-label">Capacity:</span>
              <span class="detail-value modal-capacity"></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Price:</span>
              <span class="detail-value modal-price"></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Best For:</span>
              <span class="detail-value modal-bestfor"></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Location:</span>
              <span class="detail-value modal-location"></span>
            </div>
          </div>
          <div class="modal-amenities">
            <h4>Amenities & Features:</h4>
            <ul class="modal-amenities-list"></ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr>

  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="footer-col">
          <h4>Company</h4>
          <ul>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
          </ul>
        </div>
        
        <div class="footer-col">
          <h4>Help</h4>
          <ul>
            <li><a href="faq.php">FAQ</a></li>
            <li><a href="faq.php#payment">Payment Options</a></li>
            <li><a href="faq.php#cancellation">Cancellation Policy</a></li>
            <li><a href="faq.php#terms">Terms & Conditions</a></li>
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
            <p>📍 Nonong Casto, Lemery, Batangas, Philippines</p>
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



    