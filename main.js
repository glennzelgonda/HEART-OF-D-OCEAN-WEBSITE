/* ============================================
   HEART OF D' OCEAN RESORT - MAIN JAVASCRIPT
   Version: 2.3 (Final Fix for Home & Rooms)
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 Heart Of D\' Ocean Beach Resort - Initializing...');
  
  // Core functionality
  initLoadingAnimation();
  initMobileMenu();
  initDarkMode();
  setActivePage();
  initScrollEffect();

  // Determine Page
  const path = window.location.pathname;
  const page = path.split('/').pop();
  const isHome = page === 'index.php' || page === '' || page.endsWith('/');

  // Initialize Gallery (Only for Gallery page)
  if (page === 'gallery.php') {
    initLightbox();
  }
  
  // Initialize Rooms/Cottages Modal (Home & Rooms pages)
  if (isHome || page === 'rooms.php' || page === 'cottages.php') {
    initRoomsPage(); 
    // Enable lightbox for room details only if NOT on home page
    if (!isHome) { 
        initLightbox();
    }
  }

  if (page === 'booking.php') {
    initBookingPage();
  }
  
  console.log('✅ Heart Of D\' Ocean Beach Resort website loaded successfully!');
});


/**
 * Loading animation
 */
function initLoadingAnimation() {
  try {
    const loadingSpinner = document.createElement('div');
    loadingSpinner.className = 'loading-spinner';
    loadingSpinner.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loadingSpinner);

    window.addEventListener('load', () => {
      setTimeout(() => {
        loadingSpinner.classList.add('hidden');
        const mainContent = document.querySelector('main');
        if (mainContent) {
          mainContent.classList.add('fade-in');
        }
      }, 1000);
    });
  } catch (error) {
    console.error('Error initializing loading animation:', error);
  }
}

/**
 * Mobile menu functionality
 */
function initMobileMenu() {
  try {
    const menuBtn = document.getElementById('menuBtn');
    const mainNav = document.getElementById('mainNav');
    const closeMenu = document.getElementById('closeMenu');

    if (menuBtn && mainNav && closeMenu) {
      menuBtn.addEventListener('click', () => {
        mainNav.classList.add('active');
        document.body.style.overflow = 'hidden';
      });

      closeMenu.addEventListener('click', () => {
        mainNav.classList.remove('active');
        document.body.style.overflow = 'auto';
      });

      const navLinks = document.querySelectorAll('.nav a');
      navLinks.forEach(link => {
        link.addEventListener('click', () => {
          mainNav.classList.remove('active');
          document.body.style.overflow = 'auto';
        });
      });

      document.addEventListener('click', (e) => {
        if (mainNav.classList.contains('active') && 
            !mainNav.contains(e.target) && 
            e.target !== menuBtn) {
          mainNav.classList.remove('active');
          document.body.style.overflow = 'auto';
        }
      });
    }
  } catch (error) {
    console.error('Error initializing mobile menu:', error);
  }
}

/**
 * Dark mode toggle
 */
function initDarkMode() {
  try {
    const darkToggle = document.getElementById('darkToggle');

    if (darkToggle) {
      const savedTheme = localStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-theme', savedTheme);
      darkToggle.textContent = savedTheme === 'dark' ? '☀️' : '🌙';

      darkToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        darkToggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
        localStorage.setItem('theme', newTheme);
      });
    }
  } catch (error) {
    console.error('Error initializing dark mode:', error);
  }
}

/**
 * Set active page indicator
 */
function setActivePage() {
  try {
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const navLinks = document.querySelectorAll('.nav a');
    
    navLinks.forEach(link => {
      link.classList.remove('active');
      const linkPage = link.getAttribute('href');
      
      if ((currentPage === 'index.php' || currentPage === '') && 
          (linkPage === 'index.php' || linkPage === '' || linkPage === '/')) {
        link.classList.add('active');
      }
      else if (linkPage === currentPage) {
        link.classList.add('active');
      }
    });
  } catch (error) {
    console.error('Error setting active page:', error);
  }
}

/**
 * Header scroll effect
 */
function initScrollEffect() {
  try {
    window.addEventListener('scroll', () => {
      const header = document.querySelector('.site-header');
      if (header) {
        if (window.scrollY > 50) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      }
    });
  } catch (error) {
    console.error('Error initializing scroll effect:', error);
  }
}

// ========== 3. LIGHTBOX & GALLERY FUNCTIONALITY ==========

function initLightbox() {
  try {
    const images = document.querySelectorAll('.masonry img, .grid img, .gallery-preview img, .photos-grid img, .gallery-img');
    
    images.forEach((img, index) => {
      img.addEventListener('click', function() {
        openLightbox(this.src, this.alt, index);
      });
      img.setAttribute('loading', 'lazy');
    });
    
    console.log(`📸 Lightbox initialized for ${images.length} images`);
  } catch (error) {
    console.error('Error initializing lightbox:', error);
  }
}

function openLightbox(imageSrc, imageAlt, currentIndex) {
  try {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox-enhanced';
    lightbox.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.95);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    `;

    const lightboxContent = document.createElement('div');
    lightboxContent.className = 'lightbox-content';
    lightboxContent.style.cssText = `
      position: relative;
      max-width: 90%;
      max-height: 90%;
      display: flex;
      align-items: center;
      justify-content: center;
    `;

    const img = document.createElement('img');
    img.src = imageSrc;
    img.alt = imageAlt;
    img.style.cssText = `
      max-width: 100%;
      max-height: 90vh;
      border-radius: 10px;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.5);
      cursor: grab;
      transition: transform 0.3s ease, opacity 0.3s ease;
    `;

    const prevBtn = createLightboxButton('‹', 'lightbox-prev', '-60px');
    const nextBtn = createLightboxButton('›', 'lightbox-next', null, '-60px');
    const closeBtn = createCloseButton();

    const imageCounter = document.createElement('div');
    imageCounter.className = 'lightbox-counter';
    imageCounter.style.cssText = `
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      color: white;
      background: rgba(0,0,0,0.5);
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.9rem;
      backdrop-filter: blur(10px);
    `;

    const allImages = document.querySelectorAll('.masonry img, .grid img, .gallery-preview img, .photos-grid img, .gallery-img');
    imageCounter.textContent = `${currentIndex + 1} / ${allImages.length}`;

    lightboxContent.appendChild(img);
    lightboxContent.appendChild(prevBtn);
    lightboxContent.appendChild(nextBtn);
    lightbox.appendChild(lightboxContent);
    lightbox.appendChild(closeBtn);
    lightbox.appendChild(imageCounter);
    document.body.appendChild(lightbox);

    setTimeout(() => {
      lightbox.style.opacity = '1';
    }, 10);

    closeBtn.addEventListener('click', () => closeLightbox(lightbox));
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) closeLightbox(lightbox);
    });

    function navigate(direction) {
      let newIndex = currentIndex + direction;
      if (newIndex < 0) newIndex = allImages.length - 1;
      if (newIndex >= allImages.length) newIndex = 0;
      
      img.style.opacity = '0';
      setTimeout(() => {
        img.src = allImages[newIndex].src;
        img.alt = allImages[newIndex].alt;
        imageCounter.textContent = `${newIndex + 1} / ${allImages.length}`;
        img.style.opacity = '1';
        currentIndex = newIndex;
      }, 200);
    }

    prevBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navigate(-1);
    });

    nextBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navigate(1);
    });

    function handleKeydown(e) {
      switch(e.key) {
        case 'Escape': closeLightbox(lightbox); break;
        case 'ArrowLeft': navigate(-1); break;
        case 'ArrowRight': navigate(1); break;
      }
    }

    document.addEventListener('keydown', handleKeydown);
    
    lightbox.addEventListener('close', () => {
      document.removeEventListener('keydown', handleKeydown);
    });

  } catch (error) {
    console.error('Error opening lightbox:', error);
  }
}

function createLightboxButton(text, className, left = null, right = null) {
  const btn = document.createElement('button');
  btn.innerHTML = text;
  btn.className = `lightbox-nav ${className}`;
  btn.style.cssText = `
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 2.5rem;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    ${left ? `left: ${left};` : ''}
    ${right ? `right: ${right};` : ''}
  `;
  
  btn.addEventListener('mouseenter', () => {
    btn.style.transform = 'translateY(-50%) scale(1.1)';
    btn.style.background = 'rgba(255,255,255,0.3)';
  });
  
  btn.addEventListener('mouseleave', () => {
    btn.style.transform = 'translateY(-50%) scale(1)';
    btn.style.background = 'rgba(255,255,255,0.2)';
  });
  
  return btn;
}

function createCloseButton() {
  const btn = document.createElement('button');
  btn.innerHTML = '✕';
  btn.className = 'lightbox-close';
  btn.style.cssText = `
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(0,0,0,0.5);
    border: none;
    color: white;
    font-size: 1.8rem;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10001;
  `;
  
  btn.addEventListener('mouseenter', () => {
    btn.style.transform = 'scale(1.1)';
    btn.style.background = 'rgba(0,0,0,0.7)';
  });
  
  btn.addEventListener('mouseleave', () => {
    btn.style.transform = 'scale(1)';
    btn.style.background = 'rgba(0,0,0,0.5)';
  });
  
  return btn;
}

function closeLightbox(lightbox) {
  lightbox.style.opacity = '0';
  setTimeout(() => {
    if (lightbox.parentNode) {
      lightbox.parentNode.removeChild(lightbox);
    }
  }, 300);
}

// ========== 4. ROOMS & COTTAGES FUNCTIONALITY ==========

const COTTAGE_DATA = {
  "WHITE HOUSE": {
    image: "images&vids/WhiteHouse.png",
    description: "Luxurious beachfront accommodation with premium amenities and stunning ocean views.",
    capacity: "Up to 15 guests",
    price: "₱30,000 per night",
    bestFor: "Large families, groups, special events",
    location: "Beachfront",
    amenities: ["Air Conditioning", "Private Bathroom", "Ocean View", "Kitchen", "Living Area", "Free WiFi", "Private Pool"],
    gallery: ["images&vids/WhiteHouse.png", "images&vids/White1.jpg", "images&vids/White2.jpg", "images&vids/White3.jpg"]
  },
  "PENTHOUSE": {
    image: "images&vids/PentHouse.jpg",
    description: "Exclusive top-floor suite with panoramic views and premium luxury features.",
    capacity: "Up to 8 guests",
    price: "₱12,800 per night",
    bestFor: "Couples, small families, business travelers",
    location: "Top floor",
    amenities: ["Air Conditioning", "Private Balcony", "City View", "Mini Bar", "Smart TV", "Free WiFi", "Jacuzzi"],
    gallery: ["images&vids/PentHouse.jpg", "images&vids/Penthouse1.jpg", "images&vids/Penthouse2.jpg", "images&vids/Penthouse3.jpg", "images&vids/Penthouse4.jpg"]
  },
  "AQUA CLASS": {
    image: "images&vids/Aqua.jpg",
    description: "Modern accommodation with aquatic-themed design and comfortable amenities.",
    capacity: "Up to 6 guests",
    price: "₱11,800 per night",
    bestFor: "Families, small groups",
    location: "Garden view",
    amenities: ["Air Conditioning", "Private Bathroom", "Garden View", "Coffee Maker", "TV", "Free WiFi", "Mini Kitchen"],
    gallery: ["images&vids/Aqua.jpg", "images&vids/Aqua1.jpg", "images&vids/Aqua2.jpg", "images&vids/Aqua3.jpg", "images&vids/Aqua4.jpg", "images&vids/Aqua5.jpg", "images&vids/Aqua6.jpg", "images&vids/Aqua7.jpg", "images&vids/Aqua8.jpg"]
  },
  "HEARTSUITE": {
    image: "images&vids/HeartSuite.jpg",
    description: "Romantic suite designed for couples with special amenities.",
    capacity: "Up to 4 guests",
    price: "₱11,800 per night",
    bestFor: "Couples, honeymooners",
    location: "Private area",
    amenities: ["Air Conditioning", "Private Bathroom", "King Bed", "Romantic Decor", "Private Terrace", "Free WiFi"],
    gallery: ["images&vids/HeartSuite.jpg", "images&vids/Heart1.jpg", "images&vids/Heart2.jpg", "images&vids/Heart3.jpg", "images&vids/Heart4.jpg", "images&vids/Heart5.jpg", "images&vids/Heart6.jpg"]
  },
  "STEPH'S SKYLOUNGE 842/844": {
    image: "images&vids/SkyLounge.png",
    description: "Spacious lounge area with sky views and modern amenities.",
    capacity: "Up to 10 guests",
    price: "₱11,800 per night",
    bestFor: "Groups, events, gatherings",
    location: "Upper level",
    amenities: ["Air Conditioning", "Lounge Area", "Sky View", "Entertainment System", "Bar", "Free WiFi"],
    gallery: ["images&vids/SkyLounge.png", "images&vids/S842A.jpg", "images&vids/S842B.jpg", "images&vids/S842C.jpg", "images&vids/S842D.jpg", "images&vids/S842E.jpg", "images&vids/S842F.jpg", "images&vids/S842G.jpg", "images&vids/S842H.jpg", "images&vids/S842I.jpg"]
  },
  "DE LUXE": {
    image: "images&vids/Deluxe.jpg",
    description: "Deluxe accommodation with premium comfort and style.",
    capacity: "Up to 4 guests",
    price: "₱8,800 per night",
    bestFor: "Couples, small families",
    location: "Garden area",
    amenities: ["Air Conditioning", "Private Bathroom", "Garden View", "TV", "Free WiFi"],
    gallery: ["images&vids/Deluxe.jpg", "images&vids/Deluxe1.jpg", "images&vids/Deluxe2.jpg", "images&vids/Deluxe3.jpg", "images&vids/Deluxe4.jpg", "images&vids/Deluxe5.jpg", "images&vids/Deluxe6.jpg"]
  },
  "BEATRICE A": {
    image: "images&vids/BeatriceA.jpg",
    description: "Comfortable and affordable accommodation option.",
    capacity: "Up to 4 guests",
    price: "₱7,800 per night",
    bestFor: "Budget travelers, small groups",
    location: "Standard area",
    amenities: ["Air Conditioning", "Private Bathroom", "TV", "Free WiFi"],
    gallery: ["images&vids/BeatriceA1.jpg", "images&vids/BeatriceA2.jpg", "images&vids/BeatriceA3.jpg", "images&vids/BeatriceA4.jpg"]
  },
  "BEATRICE B": {
    image: "images&vids/BeatriceB.jpg",
    description: "Economical option with basic amenities.",
    capacity: "Up to 4 guests",
    price: "₱6,800 per night",
    bestFor: "Budget travelers, backpackers",
    location: "Standard area",
    amenities: ["Air Conditioning", "Private Bathroom", "TV", "Free WiFi"],
    gallery: ["images&vids/BeatriceB1.jpg", "images&vids/BeatriceB2.jpg", "images&vids/BeatriceB3.jpg"]
  },
  "CONCIERGE 815/819": {
    image: "images&vids/Concierge.jpg",
    description: "Twin accommodation with shared amenities.",
    capacity: "Up to 6 guests",
    price: "₱8,800 per night",
    bestFor: "Groups, friends",
    location: "Concierge area",
    amenities: ["Air Conditioning", "Private Bathroom", "TV", "Free WiFi", "Shared Kitchen"],
    gallery: ["images&vids/Concierge.jpg", "images&vids/Concierge1.jpg", "images&vids/Concierge2.jpg", "images&vids/Concierge3.jpg", "images&vids/Concierge4.jpg", "images&vids/Concierge5.jpg", "images&vids/Concierge6.jpg"]
  },
  "PREMIUM 838": {
    image: "images&vids/Premium838.jpg",
    description: "Premium accommodation with enhanced features.",
    capacity: "Up to 4 guests",
    price: "₱7,800 per night",
    bestFor: "Small families, couples",
    location: "Premium area",
    amenities: ["Air Conditioning", "Private Bathroom", "TV", "Mini Fridge", "Free WiFi"],
    gallery: ["images&vids/Premium838.jpg", "images&vids/P838A.jpg", "images&vids/P838B.jpg", "images&vids/P838C.jpg", "images&vids/P838D.jpg"]
  },
  "PREMIUM 840": {
    image: "images&vids/Premium840.jpg",
    description: "Comfortable and spacious accommodation with modern amenities.",
    capacity: "Up to 4 guests",
    price: "₱8,800 per night",
    bestFor: "Small families, couples",
    location: "Resort area",
    amenities: ["Air Conditioning", "Private Bathroom", "TV", "Mini Fridge", "Free WiFi"],
    gallery: ["images&vids/Premium840.jpg", "images&vids/P840A.jpg", "images&vids/P840B.jpg", "images&vids/P840C.jpg", "images&vids/P840D.jpg", "images&vids/P840E.jpg"]
  },
  "GIANT KUBO": {
    image: "images&vids/GiantKubo.jpg",
    description: "Traditional Filipino hut with modern comforts.",
    capacity: "Up to 6 guests",
    price: "₱6,800 per night",
    bestFor: "Families, groups, cultural experience",
    location: "Garden area",
    amenities: ["Fan", "Traditional Design", "Garden View", "Native Materials", "Free WiFi"],
    gallery: ["images&vids/GiantKubo.jpg", "images&vids/Gkubo1.jpg", "images&vids/Gkubo2.jpg", "images&vids/Gkubo3.jpg", "images&vids/Gkubo4.jpg", "images&vids/Gkubo5.jpg"]
  },
  "SEASIDE (WHOLE/HALF)": {
    image: "images&vids/SeaSide.jpg",
    description: "Beachfront accommodation with direct sea access.",
    capacity: "20 to 40 guests",
    price: "WHOLE - ₱6,800 per night; HALF - ₱3,400 per night",
    bestFor: "Families, beach lovers",
    location: "Beachfront",
    amenities: ["Air Conditioning", "Private Bathroom", "Sea View", "Beach Access", "Free WiFi"],
    gallery: ["images&vids/SeaSide.jpg", "images&vids/SeaSide1.jpg", "images&vids/SeaSide2.jpg", "images&vids/SeaSide3.jpg", "images&vids/SeaSide4.jpg"]
  },
  "BAMBOO KUBO": {
    image: "images&vids/BambooKubo.jpg",
    description: "Authentic bamboo hut experience with basic amenities.",
    capacity: "Up to 4 guests",
    price: "₱2,800 per night",
    bestFor: "Backpackers, budget travelers",
    location: "Garden area",
    amenities: ["Fan", "Shared Bathroom", "Traditional Design", "Garden View", "Free WiFi"],
    gallery: ["images&vids/BambooKubo.jpg", "images&vids/Bamboo.jpg"]
  }
};

/**
 * Initialize rooms page functionality
 */
function initRoomsPage() {
  try {
    const path = window.location.pathname;
    const page = path.split('/').pop();
    const isHomePage = page === 'index.php' || page === '' || page.endsWith('/');
    
    // Both pages use the same modal ID 'cottageModal'
    const modal = document.getElementById('cottageModal');
    if (!modal) return;
    
    // Determine prefix (-home for index.php, none for rooms.php)
    const prefix = isHomePage ? '-home' : '';
    
    // Select elements based on the prefix
    const modalImage = document.querySelector(`.modal-image${prefix}`);
    const modalTitle = document.querySelector(`.modal-title${prefix}`);
    const modalDescription = document.querySelector(`.modal-description${prefix}`);
    const modalCapacity = document.querySelector(`.modal-capacity${prefix}`);
    const modalPrice = document.querySelector(`.modal-price${prefix}`);
    const modalBestFor = document.querySelector(`.modal-bestfor${prefix}`);
    const modalLocation = document.querySelector(`.modal-location${prefix}`);
    const modalAmenitiesList = document.querySelector(`.modal-amenities-list${prefix}`);
    
    const closeModalBtn = modal.querySelector('.close-modal');
    const modalTriggers = document.querySelectorAll('.view-details, .image-modal-trigger');
    
    if (!modalTriggers.length) return;

    // OPEN MODAL FUNCTION
    const openModal = (cottage, cottageName) => {
        // Set main data
        if (modalImage) {
            modalImage.src = cottage.image;
            modalImage.alt = cottageName;
        }
        if (modalTitle) modalTitle.textContent = cottageName;
        if (modalDescription) modalDescription.textContent = cottage.description;
        if (modalCapacity) modalCapacity.textContent = cottage.capacity;
        if (modalPrice) modalPrice.textContent = cottage.price;
        if (modalBestFor) modalBestFor.textContent = cottage.bestFor;
        if (modalLocation) modalLocation.textContent = cottage.location;
        
        // Populate Amenities
        if (modalAmenitiesList) {
            modalAmenitiesList.innerHTML = '';
            cottage.amenities.forEach(amenity => {
                const li = document.createElement('li');
                li.textContent = amenity;
                modalAmenitiesList.appendChild(li);
            });
        }
        
        // Populate Gallery (ONLY for rooms.php logic, skipped on Home if not needed)
        // Note: Home page modal usually doesn't have the gallery slider in your HTML
        if (!isHomePage) {
            const galleryScroll = document.getElementById('galleryScroll');
            if (galleryScroll) {
                galleryScroll.innerHTML = '';
                cottage.gallery.forEach((imageSrc, index) => {
                    const galleryItem = document.createElement('div');
                    galleryItem.className = 'gallery-item';
                    if (index === 0) galleryItem.classList.add('active');

                    galleryItem.innerHTML = `<img src="${imageSrc}" alt="${cottageName} - Image ${index + 1}">`;
                    
                    galleryItem.addEventListener('click', () => {
                        if (modalImage) {
                            modalImage.style.opacity = '0.5';
                            setTimeout(() => {
                                modalImage.src = imageSrc;
                                modalImage.style.opacity = '1';
                            }, 200);
                        }
                        galleryScroll.querySelectorAll('.gallery-item').forEach(item => item.classList.remove('active'));
                        galleryItem.classList.add('active');
                    });
                    
                    galleryScroll.appendChild(galleryItem);
                });
            }
        }
        
        // Show Modal with Animation
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
    };

    // EVENT LISTENERS
    modalTriggers.forEach(trigger => {
      trigger.addEventListener('click', function(e) {
        if (this.tagName === 'A') e.preventDefault();
        
        const cottageName = this.getAttribute('data-cottage');
        if (cottageName && COTTAGE_DATA[cottageName]) {
            openModal(COTTAGE_DATA[cottageName], cottageName);
        }
      });
    });

    // Close Function
    const closeModal = () => {
        modal.classList.remove('active'); // Fade out
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 400); // Wait for transition
    };

    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    
    window.addEventListener('click', (event) => {
      if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });

    console.log(`🏠 ${isHomePage ? 'Home' : 'Rooms'} page modal initialized`);
  } catch (error) {
    console.error('Error initializing rooms page:', error);
  }
}

// ========== 5. BOOKING SYSTEM FUNCTIONS ==========

function initBookingPage() {
  try {
    setMinimumDates();
    calculateStay();
    setDefaultCheckoutDate();
    togglePaymentOption();
    setupResetForm();
    addBookingEventListeners();
    
    console.log('📅 Booking page functionality initialized');
  } catch (error) {
    console.error('Error initializing booking page:', error);
  }
}

function setMinimumDates() {
  try {
    const today = new Date().toISOString().split('T')[0];
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    
    if (checkinInput) {
      checkinInput.min = today;
      checkinInput.value = today;
    }
    
    if (checkoutInput) {
      checkoutInput.min = today;
    }
  } catch (error) {
    console.error('Error setting minimum dates:', error);
  }
}

function setDefaultCheckoutDate() {
  try {
    const checkoutInput = document.getElementById('checkout');
    if (checkoutInput && !checkoutInput.value) {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      checkoutInput.value = tomorrow.toISOString().split('T')[0];
    }
  } catch (error) {
    console.error('Error setting default checkout date:', error);
  }
}

function togglePaymentOption() {
  try {
    const paymentMethod = document.getElementById('paymentMethod');
    const qrSection = document.getElementById('qrSection');
    
    if (!paymentMethod || !qrSection) return;
    
    const isGCash = paymentMethod.value === 'pay-now';
    qrSection.style.display = isGCash ? 'block' : 'none';
    
    if (isGCash) updateQRAmount();
    
    const gcashFields = ['receiptUpload', 'gcashName', 'gcashNumber', 'paymentReference', 'paymentDate'];
    gcashFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) field.required = isGCash;
    });
  } catch (error) {
    console.error('Error toggling payment option:', error);
  }
}

function updateQRAmount() {
  try {
    const totalAmount = document.getElementById('totalAmount');
    const qrAmount = document.getElementById('qrAmount');
    const qrReference = document.getElementById('qrReference');
    
    if (totalAmount && qrAmount) qrAmount.textContent = totalAmount.textContent;
    if (qrReference) qrReference.textContent = 'RESORT' + Date.now().toString().slice(-6);
  } catch (error) {
    console.error('Error updating QR amount:', error);
  }
}

function calculateStay() {
  try {
    const checkin = document.getElementById('checkin');
    const checkout = document.getElementById('checkout');
    const accommodationSelect = document.getElementById('accommodation');
    
    if (!checkin || !checkout || !accommodationSelect) return;
    
    if (!checkin.value || !checkout.value) {
      hidePriceBreakdown();
      return;
    }
    
    const checkinDate = new Date(checkin.value);
    const checkoutDate = new Date(checkout.value);
    
    if (checkoutDate <= checkinDate) {
      hidePriceBreakdown();
      return;
    }
    
    const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
    const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    const selectedOption = accommodationSelect.options[accommodationSelect.selectedIndex];
    const pricePerNight = selectedOption ? parseInt(selectedOption.getAttribute('data-price')) || 0 : 0;
    
    if (nights > 0 && pricePerNight > 0) {
      updatePriceBreakdown(pricePerNight, nights, pricePerNight * nights);
    } else {
      hidePriceBreakdown();
    }
  } catch (error) {
    console.error('Error calculating stay:', error);
    hidePriceBreakdown();
  }
}

function updatePriceBreakdown(pricePerNight, nights, totalPrice) {
  try {
    const accommodationPrice = document.getElementById('accommodationPrice');
    const nightsCount = document.getElementById('nightsCount');
    const totalAmount = document.getElementById('totalAmount');
    const priceBreakdown = document.getElementById('priceBreakdown');
    
    if (accommodationPrice) accommodationPrice.textContent = `₱${pricePerNight.toLocaleString()}`;
    if (nightsCount) nightsCount.textContent = nights;
    if (totalAmount) totalAmount.textContent = `₱${totalPrice.toLocaleString()}`;
    if (priceBreakdown) priceBreakdown.style.display = 'block';
    
    if (document.getElementById('paymentMethod')?.value === 'pay-now') updateQRAmount();
  } catch (error) {
    console.error('Error updating price breakdown:', error);
  }
}

function hidePriceBreakdown() {
  const priceBreakdown = document.getElementById('priceBreakdown');
  if (priceBreakdown) priceBreakdown.style.display = 'none';
}

// ========== 6. AVAILABILITY CHECK ==========

function checkCottageAvailability() {
  const checkin = document.getElementById('checkin').value;
  const checkout = document.getElementById('checkout').value;
  const accommodationSelect = document.getElementById('accommodation');
  const bookBtn = document.getElementById('bookNowButton');
  
  if (!accommodationSelect || !checkin || !checkout) return;
  if (new Date(checkout) <= new Date(checkin)) return;
  
  if (this._availabilityCheckTimeout) clearTimeout(this._availabilityCheckTimeout);
  
  const cacheKey = `${checkin}-${checkout}`;
  const cachedResult = sessionStorage.getItem(`availability_${cacheKey}`);
  const cacheTime = sessionStorage.getItem(`availability_time_${cacheKey}`);
  
  if (cachedResult && cacheTime && (Date.now() - parseInt(cacheTime)) < 5000) {
    try {
      updateCottageOptions(JSON.parse(cachedResult));
      return;
    } catch (e) {}
  }

  if(bookBtn) {
    bookBtn.disabled = true;
    bookBtn.innerHTML = 'Checking Availability...';
  }
  
  if (accommodationSelect) {
    accommodationSelect.style.opacity = '0.7';
    accommodationSelect.disabled = true;
  }

  this._availabilityCheckTimeout = setTimeout(() => {
    const formData = new FormData();
    formData.append('checkin_date', checkin);
    formData.append('checkout_date', checkout);

    fetch('check_availability_range.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
      const bookedList = (data.booked_cottages || []).map(c => c.toLowerCase());
      sessionStorage.setItem(`availability_${cacheKey}`, JSON.stringify(bookedList));
      sessionStorage.setItem(`availability_time_${cacheKey}`, Date.now().toString());
      updateCottageOptions(bookedList);
    })
    .catch(error => console.error('Availability check failed:', error))
    .finally(() => {
      if (accommodationSelect) {
        accommodationSelect.disabled = false;
        accommodationSelect.style.opacity = '1';
      }
      updateBookButton();
    });
  }, 300);
}

function updateCottageOptions(bookedList) {
  const accommodationSelect = document.getElementById('accommodation');
  if (!accommodationSelect) return;

  const options = accommodationSelect.querySelectorAll('option');
  const currentlySelected = accommodationSelect.value.toLowerCase();
  let hasAvailableOptions = false;

  options.forEach(option => {
    if (option.value === "") return;

    if (!option.hasAttribute('data-original-text')) {
      option.setAttribute('data-original-text', option.textContent.replace(' — Not Available', ''));
    }

    const originalText = option.getAttribute('data-original-text');
    const cottageValue = option.value.toLowerCase();

    option.disabled = false;
    option.style.color = '';
    option.textContent = originalText;

    if (bookedList.includes(cottageValue)) {
      option.disabled = true;
      option.textContent = originalText + " — Not Available";
      option.style.color = '#999';
      
      if (currentlySelected === cottageValue) {
        accommodationSelect.value = "";
        showBookingAlert(originalText);
      }
    } else {
      hasAvailableOptions = true;
    }
  });

  if (!hasAvailableOptions) showNoAvailabilityMessage();
}

function showBookingAlert(cottageName) {
  const alertDiv = document.getElementById('bookingDisabledMessage');
  if (alertDiv) {
    alertDiv.style.display = 'block';
    alertDiv.innerHTML = `The cottage "${cottageName}" is unavailable for these dates. Please select another.`;
  } else {
    alert(`The cottage "${cottageName}" is unavailable for these dates.`);
  }
}

function showNoAvailabilityMessage() {
  const alertDiv = document.getElementById('bookingDisabledMessage');
  if (alertDiv) {
    alertDiv.style.display = 'block';
    alertDiv.innerHTML = 'All cottages are fully booked for these dates. Please try different dates.';
  }
}

function updateBookButton() {
  const accommodationSelect = document.getElementById('accommodation');
  const bookNowButton = document.getElementById('bookNowButton');
  const bookingDisabledMessage = document.getElementById('bookingDisabledMessage');
  
  if (!accommodationSelect || !bookNowButton) return;
  
  const selectedOption = accommodationSelect.options[accommodationSelect.selectedIndex];
  const isSelected = selectedOption && selectedOption.value !== '';
  const isBooked = selectedOption && selectedOption.disabled;
  
  if (isSelected) {
    if (isBooked) {
      bookNowButton.disabled = true;
      bookNowButton.innerHTML = 'Unavailable';
      bookNowButton.style.backgroundColor = '#ccc';
      bookNowButton.style.cursor = 'not-allowed';
      if (bookingDisabledMessage) {
        bookingDisabledMessage.style.display = 'block';
        bookingDisabledMessage.textContent = 'This cottage is already booked.';
      }
    } else {
      bookNowButton.disabled = false;
      bookNowButton.innerHTML = 'Book Now';
      bookNowButton.style.backgroundColor = '';
      bookNowButton.style.cursor = 'pointer';
      if (bookingDisabledMessage) bookingDisabledMessage.style.display = 'none';
    }
  } else {
    bookNowButton.disabled = true;
    bookNowButton.innerHTML = 'Select Accommodation';
    bookNowButton.style.backgroundColor = ''; 
  }
}

function resetCottageAvailability() {
  const accommodationSelect = document.getElementById('accommodation');
  if (!accommodationSelect) return;
  
  const options = accommodationSelect.options;
  for (let i = 0; i < options.length; i++) {
    if (options[i].value) {
      const originalText = options[i].getAttribute('data-original-text') || options[i].textContent.replace(' — Not Available', '');
      options[i].textContent = originalText;
      options[i].disabled = false;
      options[i].style.color = '';
    }
  }
}

// ========== 7. FORM HANDLING & UTILITY ==========

function setupResetForm() {
  const resetBtn = document.getElementById('resetForm');
  if (!resetBtn) return;
  
  resetBtn.addEventListener('click', function() {
    if (confirm('Are you sure you want to reset the form?')) {
      document.getElementById('bookingForm').reset();
      hidePriceBreakdown();
      const qrSection = document.getElementById('qrSection');
      if (qrSection) qrSection.style.display = 'none';
      const bookingDisabledMessage = document.getElementById('bookingDisabledMessage');
      if (bookingDisabledMessage) bookingDisabledMessage.style.display = 'none';
      
      resetCottageAvailability();
      updateBookButton();
      setMinimumDates();
      setDefaultCheckoutDate();
    }
  });
}

function addBookingEventListeners() {
  const checkinInput = document.getElementById('checkin');
  const checkoutInput = document.getElementById('checkout');
  const accommodationSelect = document.getElementById('accommodation');
  const paymentMethodSelect = document.getElementById('paymentMethod');
  
  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), wait);
    };
  }
  
  const debouncedCheckAvailability = debounce(checkCottageAvailability, 500);
  
  if (checkinInput) {
    checkinInput.addEventListener('change', function() {
      calculateStay();
      debouncedCheckAvailability();
      updateBookButton();
    });
  }
  
  if (checkoutInput) {
    checkoutInput.addEventListener('change', function() {
      calculateStay();
      debouncedCheckAvailability();
      updateBookButton();
    });
  }
  
  if (accommodationSelect) {
    accommodationSelect.addEventListener('change', function() {
      calculateStay();
      updateBookButton();
    });
  }
  
  if (paymentMethodSelect) {
    paymentMethodSelect.addEventListener('change', togglePaymentOption);
  }
}

window.addEventListener('error', function(event) {
  console.error('Uncaught error:', event.error);
});