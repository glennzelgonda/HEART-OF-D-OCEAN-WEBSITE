<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Gallery — Heart Of D' Ocean Beach Resort</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&family=Pacifico&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <style>
    /* Gallery viewer specific styles */
    .masonry {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      grid-gap: 15px;
      margin-top: 30px;
    }
    
    .masonry img {
      width: 100%;
      height: auto;
      border-radius: 8px;
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
      object-fit: cover;
      aspect-ratio: 4/3;
    }
    
    .masonry img:hover {
      transform: scale(1.03);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Image viewer modal */
    .image-viewer {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.9);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    
    .image-viewer.active {
      display: flex;
    }
    
    .image-viewer-content {
      max-width: 90%;
      max-height: 90%;
      position: relative;
    }
    
    .image-viewer-img {
      max-width: 100%;
      max-height: 90vh;
      border-radius: 4px;
    }
    
    .image-viewer-caption {
      color: white;
      text-align: center;
      margin-top: 15px;
      font-size: 1.2rem;
    }
    
    /* Navigation buttons */
    .image-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: none;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      font-size: 24px;
      cursor: pointer;
      transition: background 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .image-nav:hover {
      background: rgba(255, 255, 255, 0.3);
    }
    
    .nav-prev {
      left: 20px;
    }
    
    .nav-next {
      right: 20px;
    }
    
    /* Close button */
    .image-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: none;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      font-size: 28px;
      cursor: pointer;
      transition: background 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .image-close:hover {
      background: rgba(255, 255, 255, 0.3);
    }
    
    /* Image counter */
    .image-counter {
      position: absolute;
      top: 20px;
      left: 20px;
      color: white;
      font-size: 1.1rem;
      background: rgba(0, 0, 0, 0.5);
      padding: 8px 15px;
      border-radius: 20px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .masonry {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      }
      
      .image-nav {
        width: 50px;
        height: 50px;
        font-size: 20px;
      }
      
      .nav-prev {
        left: 10px;
      }
      
      .nav-next {
        right: 10px;
      }
      
      .image-close {
        width: 40px;
        height: 40px;
        font-size: 22px;
        top: 10px;
        right: 10px;
      }
      
      .image-counter {
        top: 10px;
        left: 10px;
        font-size: 1rem;
        padding: 6px 12px;
      }
    }
    
    @media (max-width: 480px) {
      .masonry {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      }
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="logo" href="index.php">
        <img src="images&vids/logo1.png" alt="Heart Of D' Ocean Logo">
      </a>
    
      <nav class="nav" id="mainNav">
        <a href="index.php">Home</a>
        <a href="rooms.php">Cottages</a>
        <a href="gallery.php" class="active">Gallery</a>
        <a href="booking.php" class="cta">Book Now</a>
        <button id="darkToggle" class="icon-btn" aria-label="Toggle dark">🌙</button>
      </nav>
      <button id="menuBtn" class="hamburger" aria-label="menu">☰</button>
    </div>
  </header>

  <main class="container">
    <h1 class="gallery-title">Our Gallery</h1>
    <p class="subtitle">Click on any image to view it full screen</p>
    
    <div class="masonry">
      <!-- Gallery Images -->
      <img src="images&vids/1.jpg" alt="Resort photo 1" data-index="1">
      <img src="images&vids/2.jpg" alt="Resort photo 2" data-index="2">
      <img src="images&vids/3.jpg" alt="Resort photo 3" data-index="3">
      <img src="images&vids/4.jpg" alt="Resort photo 4" data-index="4">
      <img src="images&vids/5.jpg" alt="Resort photo 5" data-index="5">
      <img src="images&vids/6.jpg" alt="Resort photo 6" data-index="6">
      <img src="images&vids/7.jpg" alt="Resort photo 7" data-index="7">
      <img src="images&vids/8.jpg" alt="Resort photo 8" data-index="8">
      <img src="images&vids/9.jpg" alt="Resort photo 9" data-index="9">
      <img src="images&vids/10.jpg" alt="Resort photo 10" data-index="10">
      <img src="images&vids/11.jpg" alt="Resort photo 11" data-index="11">
      <img src="images&vids/12.jpg" alt="Resort photo 12" data-index="12">
      <img src="images&vids/13.jpg" alt="Resort photo 13" data-index="13">
      <img src="images&vids/14.jpg" alt="Resort photo 14" data-index="14">
      <img src="images&vids/15.jpg" alt="Resort photo 15" data-index="15">
      <img src="images&vids/16.jpg" alt="Resort photo 16" data-index="16">
    </div>
  </main>

  <!-- Image Viewer Modal -->
  <div class="image-viewer" id="imageViewer">
    <button class="image-close" id="closeViewer" aria-label="Close image viewer">×</button>
    <div class="image-counter" id="imageCounter">1 / 16</div>
    
    <button class="image-nav nav-prev" id="prevImage" aria-label="Previous image">
      <i class="fas fa-chevron-left"></i>
    </button>
    
    <div class="image-viewer-content">
      <img class="image-viewer-img" id="viewerImage" src="" alt="">
      <div class="image-viewer-caption" id="imageCaption">Resort photo</div>
    </div>
    
    <button class="image-nav nav-next" id="nextImage" aria-label="Next image">
      <i class="fas fa-chevron-right"></i>
    </button>
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

  <script>
    // Image viewer functionality
    document.addEventListener('DOMContentLoaded', function() {
      const images = document.querySelectorAll('.masonry img');
      const viewer = document.getElementById('imageViewer');
      const viewerImage = document.getElementById('viewerImage');
      const imageCaption = document.getElementById('imageCaption');
      const imageCounter = document.getElementById('imageCounter');
      const closeBtn = document.getElementById('closeViewer');
      const prevBtn = document.getElementById('prevImage');
      const nextBtn = document.getElementById('nextImage');
      
      let currentIndex = 0;
      
      // Open image viewer when an image is clicked
      images.forEach((img, index) => {
        img.addEventListener('click', () => {
          currentIndex = index;
          updateViewer();
          viewer.classList.add('active');
          document.body.style.overflow = 'hidden'; 
        });
      });
      
      // Close viewer
      closeBtn.addEventListener('click', closeViewer);
      
      // Close viewer when clicking outside the image
      viewer.addEventListener('click', (e) => {
        if (e.target === viewer) {
          closeViewer();
        }
      });
      
      // Navigation
      prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateViewer();
      });
      
      nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % images.length;
        updateViewer();
      });
      
      // Keyboard navigation
      document.addEventListener('keydown', (e) => {
        if (!viewer.classList.contains('active')) return;
        
        switch(e.key) {
          case 'Escape':
            closeViewer();
            break;
          case 'ArrowLeft':
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            updateViewer();
            break;
          case 'ArrowRight':
            currentIndex = (currentIndex + 1) % images.length;
            updateViewer();
            break;
        }
      });
      
      // Update viewer with current image
      function updateViewer() {
        const currentImg = images[currentIndex];
        viewerImage.src = currentImg.src;
        viewerImage.alt = currentImg.alt;
        imageCaption.textContent = currentImg.alt;
        imageCounter.textContent = `${currentIndex + 1} / ${images.length}`;
      }
      
      // Close viewer function
      function closeViewer() {
        viewer.classList.remove('active');
        document.body.style.overflow = 'auto'; // Re-enable scrolling
      }
      
      // Add some CSS for the subtitle
      const style = document.createElement('style');
      style.textContent = `
        .subtitle {
          color: #666;
          font-size: 1.1rem;
          margin-bottom: 20px;
          text-align: center;
        }
      `;
      document.head.appendChild(style);
    });
  </script>
  
  <script src="main.js"></script>
</body>
</html>