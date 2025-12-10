// ========== LOADING ANIMATION FUNCTIONALITY ==========
function initLoadingAnimation() {
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
}

// ========== MOBILE MENU FUNCTIONALITY ==========
function initMobileMenu() {
  const menuBtn = document.getElementById('menuBtn');
  const mainNav = document.getElementById('mainNav');
  const closeMenu = document.getElementById('closeMenu');

  if (menuBtn && mainNav && closeMenu) {
    menuBtn.addEventListener('click', () => {
      mainNav.classList.add('active');
    });

    closeMenu.addEventListener('click', () => {
      mainNav.classList.remove('active');
    });

    const navLinks = document.querySelectorAll('.nav a');
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        mainNav.classList.remove('active');
      });
    });
  }
}

// ========== DARK MODE TOGGLE ==========
function initDarkMode() {
  const darkToggle = document.getElementById('darkToggle');

  if (darkToggle) {
    darkToggle.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      
      document.documentElement.setAttribute('data-theme', newTheme);
      darkToggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
      localStorage.setItem('theme', newTheme);
    });

    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
      document.documentElement.setAttribute('data-theme', savedTheme);
      darkToggle.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
    }
  }
}

// ========== ACTIVE PAGE INDICATOR ==========
function setActivePage() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('.nav a');
  
  navLinks.forEach(link => {
    link.classList.remove('active');
    const linkPage = link.getAttribute('href');
    if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.html')) {
      link.classList.add('active');
    }
  });
}

// ========== SCROLL EFFECT FOR HEADER ==========
function initScrollEffect() {
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
}

// ========== ENHANCED LIGHTBOX FUNCTIONALITY ==========
function initLightbox() {
  const images = document.querySelectorAll('.masonry img, .grid img, .gallery-preview img');
  
  images.forEach((img, index) => {
    img.addEventListener('click', function() {
      openLightbox(this.src, this.alt, index);
    });
    img.setAttribute('loading', 'lazy');
  });
}

function openLightbox(imageSrc, imageAlt, currentIndex) {
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
    transition: transform 0.3s ease;
  `;

  const prevBtn = document.createElement('button');
  prevBtn.innerHTML = '‹';
  prevBtn.className = 'lightbox-nav lightbox-prev';
  prevBtn.style.cssText = `
    position: absolute;
    left: -60px;
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
  `;

  const nextBtn = document.createElement('button');
  nextBtn.innerHTML = '›';
  nextBtn.className = 'lightbox-nav lightbox-next';
  nextBtn.style.cssText = `
    position: absolute;
    right: -60px;
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
  `;

  const closeBtn = document.createElement('button');
  closeBtn.innerHTML = '✕';
  closeBtn.className = 'lightbox-close';
  closeBtn.style.cssText = `
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

  const allImages = document.querySelectorAll('.masonry img, .grid img, .gallery-preview img');
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

  closeBtn.addEventListener('click', () => {
    lightbox.style.opacity = '0';
    setTimeout(() => {
      lightbox.remove();
    }, 300);
  });

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      lightbox.style.opacity = '0';
      setTimeout(() => {
        lightbox.remove();
      }, 300);
    }
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
    if (e.key === 'Escape') {
      closeBtn.click();
    } else if (e.key === 'ArrowLeft') {
      navigate(-1);
    } else if (e.key === 'ArrowRight') {
      navigate(1);
    }
  }

  document.addEventListener('keydown', handleKeydown);

  lightbox.addEventListener('close', () => {
    document.removeEventListener('keydown', handleKeydown);
  });

  let touchStartX = 0;
  let touchEndX = 0;

  lightboxContent.addEventListener('touchstart', e => {
    touchStartX = e.changedTouches[0].screenX;
  });

  lightboxContent.addEventListener('touchend', e => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  });

  function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
      if (diff > 0) {
        navigate(1);
      } else {
        navigate(-1);
      }
    }
  }
}

// ========== ROOMS PAGE MODAL FUNCTIONALITY ==========
function initRoomsPage() {
      const modal = document.getElementById('cottageModal');
      const modalImage = document.querySelector('.modal-image');
      const modalTitle = document.querySelector('.modal-title');
      const modalDescription = document.querySelector('.modal-description');
      const modalCapacity = document.querySelector('.modal-capacity');
      const modalPrice = document.querySelector('.modal-price');
      const modalBestFor = document.querySelector('.modal-bestfor');
      const modalLocation = document.querySelector('.modal-location');
      const modalAmenitiesList = document.querySelector('.modal-amenities-list');
      const galleryScroll = document.getElementById('galleryScroll');
      const closeModalBtn = document.querySelector('.close-modal');
      const modalTriggers = document.querySelectorAll('.image-modal-trigger');

      // Enhanced Cottage Data with gallery items
      const cottageData = {
        "WHITE HOUSE": {
        image: "images&vids/WhiteHouse.png",
        description: "A luxurious beachfront cottage with panoramic ocean views and premium amenities. Perfect for large gatherings and special occasions.",
        capacity: "Up to 18-25 guests",
        price: "₱30,000/night",
        bestFor: "Large families, reunions, barkada",
        location: "Beachfront",
        amenities: [
          "Private balcony", "Ocean view", "5 Bedrooms", "3 Bathrooms", "Hot & cold shower",
          "Air conditioning", "Full kitchen", "Living area", "Free WiFi", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" },
          { type: "image", src: "https://images.unsplash.com/photo-1564078516393-cf04bd966897?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" },
          { type: "image", src: "https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "PENTHOUSE": {
        image: "https://images.unsplash.com/photo-1564078516393-cf04bd966897?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Our most exclusive accommodation with 360-degree views and premium luxury features. Top-floor luxury experience.",
        capacity: "Up to 12-15 guests",
        price: "₱12,800/night",
        bestFor: "Barkada, small events, luxury travelers",
        location: "Top floor",
        amenities: [
          "360-degree ocean view", "Private terrace", "3 Bedrooms", "2 Bathrooms",
          "Hot & cold shower", "Air conditioning", "Mini bar", "Free WiFi", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1564078516393-cf04bd966897?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" },
          { type: "image", src: "https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "AQUA CLASS": {
        image: "images&vids/Aqua.jpg",
        description: "Modern cottage with direct pool access and contemporary design elements. Perfect for water lovers.",
        capacity: "Up to 12-15 guests",
        price: "₱11,800/night",
        bestFor: "Families, groups, pool lovers",
        location: "Poolside",
        amenities: [
          "Direct pool access", "3 Bedrooms", "2 Bathrooms", "Air conditioning",
          "Lounge area", "Free WiFi", "Smart TV", "Mini fridge"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" },
          { type: "image", src: "https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "HEARTSUITE": {
        image: "images&vids/HeartSuite.jpg",
        description: "Romantic suite designed for couples with special touches and intimate atmosphere.",
        capacity: "Up to 12-15 guests",
        price: "₱11,800",
        bestFor: "Friends, group",
        location: "Garden view",
        amenities: [
          "Air conditioning", "King-size bed", "Lounge area", "Free WiFi",
          "Mini fridge", "Pool access"
        ],
        gallery: [
          { type: "image", src: "images&vids/HeartSuite.jpg" }
        ]
      },

      "STEPH'S SKYLOUNGE 842/844": {
        image: "https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Spacious interconnected rooms perfect for families or groups traveling together.",
        capacity: "Up to 10-12 guests",
        price: "₱11,800",
        bestFor: "Families, groups",
        location: "Upper floor with ocean view",
        amenities: [
          "Wide window view", "Air conditioning", "Hot & cold shower",
          "Free WiFi", "Dining area", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "STEPH'S 846": {
        image: "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Comfortable cottage with modern amenities and convenient access to resort facilities.",
        capacity: "Up to 8-10 guests",
        price: "₱10,000",
        bestFor: "Solo travelers, small group",
        location: "Central resort area",
        amenities: [
          "Queen-size bed", "Air conditioning", "Hot & cold shower",
          "Free WiFi", "Dining area", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "STEPH'S 848": {
        image: "https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Cozy cottage with garden views and comfortable furnishings for a relaxing stay.",
        capacity: "Up to 8-10 guests",
        price: "₱10,800",
        bestFor: "Solo travelers, small groups",
        location: "Main resort",
        amenities: [
          "Queen-size bed", "Air conditioning", "Hot & cold shower",
          "Free WiFi", "Dining area", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "DE LUXE": {
        image: "images&vids/Deluxe.jpg",
        description: "Premium accommodation with upgraded amenities and stylish interior design.",
        capacity: "Up to 8-10 guests",
        price: "₱8,800",
        bestFor: "friends, small families",
        location: "Beachfront",
        amenities: [
          "Air conditioning", "Hot & cold shower", "Free WiFi",
          "Dining area", "Pool access", "Premium bedding"
        ],
        gallery: [
          { type: "image", src: "images&vids/Deluxe.jpg" }
        ]
      },

      "BEATRICE A": {
        image: "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Charming cottage with traditional design elements and modern comforts.",
        capacity: "Up to 6-8 guests",
        price: "₱7,800",
        bestFor: "Couples, small families",
        location: "Ground floor",
        amenities: [
          "Air conditioning", "Hot & cold shower", "Free WiFi",
          "Dining area", "Pool access", "Premium bedding"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "BEATRICE B": {
        image: "https://images.unsplash.com/photo-1564501049412-61c2a3083791?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Comfortable cottage with garden access and relaxing atmosphere.",
        capacity: "Up to 6-8 guests",
        price: "₱6,800",
        bestFor: "Travelers, small families",
        location: "Ground floor",
        amenities: [
          "Air conditioning", "Hot & cold shower", "Free WiFi",
          "Dining area", "Pool access", "Premium bedding"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1564501049412-61c2a3083791?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "CONCIERGE 815/819": {
        image: "images&vids/Concierge.jpg",
        description: "Interconnected rooms with premium concierge service and exclusive amenities.",
        capacity: "Up to 6-8 guests",
        price: "₱8,800",
        bestFor: "Families, groups",
        location: "Main building",
        amenities: [
          "Spacious layout", "Air conditioning", "Work desk",
          "Pool access", "Free WiFi"
        ],
        gallery: [
          { type: "image", src: "images&vids/Concierge.jpg" }
        ]
      },

      "CONCIERGE 817": {
        image: "https://images.unsplash.com/photo-1591088398332-8a7791972843?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Premium room with dedicated concierge service and business-friendly amenities.",
        capacity: "Up to 8-10 guests",
        price: "₱9,800",
        bestFor: "Families, barkada",
        location: "Main building",
        amenities: [
          "Lounge area", "Air conditioning", "Free WiFi",
          "Pool access", "Mini fridge"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1591088398332-8a7791972843?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "PREMIUM 838": {
        image: "images&vids/Premium838.jpg",
        description: "Upgraded accommodation with premium features and stylish design.",
        capacity: "Up to 6-8 guests",
        price: "₱7,800",
        bestFor: "Couples, small groups",
        location: "Beach view",
        amenities: [
          "Beach view", "King-size bed", "Air conditioning",
          "Free WiFi", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "PREMIUM 840": {
        image: "images&vids/Premium840.jpg",
        description: "Spacious premium cottage with modern amenities and comfortable living space.",
        capacity: "Up to 6-8 guests",
        price: "₱8,800",
        bestFor: "Couples, small families",
        location: "Beach view",
        amenities: [
          "Air conditioning", "King-size bed", "Sitting area",
          "Private bathroom", "Free WiFi", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "GIANT KUBO": {
        image: "images&vids/GiantKubo.jpg",
        description: "Traditional Filipino-style cottage with modern amenities and spacious layout.",
        capacity: "Up to 12-15 guests",
        price: "₱6,800",
        bestFor: "Large families, groups",
        location: "Garden area",
        amenities: [
          "Native bamboo design", "Open-air concept", "Multiple beds",
          "Electric fan", "Pool access"
        ],
        gallery: [
          { type: "image", src: "images&vids/GiantKubo.jpg" }
        ]
      },
      "SEASIDE (WHOLE)": {
        image: "https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Complete seaside cottage with direct beach access and panoramic ocean views.",
        capacity: "Up to 14-16 guests",
        price: "₱6,800",
        bestFor: "Families, groups",
        location: "Beachfront",
        amenities: [
          "Seaside cottage", "Oceanfront", "Spacious seating", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "SEASIDE (HALF)": {
        image: "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
        description: "Cozy seaside accommodation with beach proximity and comfortable amenities.",
        capacity: "Up to 8-10 guests",
        price: "₱3,400",
        bestFor: "Couples, small families",
        location: "Beachfront",
        amenities: [
          "Open-style cottage", "Oceanfront", "Queen-size bed",
          "Kitchenette", "Pool access"
        ],
        gallery: [
          { type: "image", src: "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" }
        ]
      },

      "BAMBOO KUBO": {
        image: "images&vids/BambooKubo.jpg",
        description: "Authentic bamboo cottage offering a traditional Filipino experience with modern comforts.",
        capacity: "Up to 6-8 guests",
        price: "₱2,800",
        bestFor: "Budget groups",
        location: "Beachfront area",
        amenities: [
          "Bamboo structure", "Queen-size bed", "Pool access",
          "Ceiling fan", "Mini fridge"
        ],
        gallery: [
          { type: "image", src: "images&vids/BambooKubo.jpg" }
        ]
      }

      };

      // Open modal when cottage card is clicked
      modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
          const cottageName = this.getAttribute('data-cottage');
          const cottage = cottageData[cottageName];
          
          if (cottage) {
            // Set main image and info
            modalImage.src = cottage.image;
            modalImage.alt = cottageName;
            modalTitle.textContent = cottageName;
            modalDescription.textContent = cottage.description;
            modalCapacity.textContent = cottage.capacity;
            modalPrice.textContent = cottage.price;
            modalBestFor.textContent = cottage.bestFor;
            modalLocation.textContent = cottage.location;
            
            // Clear and populate amenities
            modalAmenitiesList.innerHTML = '';
            cottage.amenities.forEach(amenity => {
              const li = document.createElement('li');
              li.textContent = amenity;
              modalAmenitiesList.appendChild(li);
            });
            
            // Clear and populate gallery
            galleryScroll.innerHTML = '';
            if (cottage.gallery && cottage.gallery.length > 0) {
              cottage.gallery.forEach((item, index) => {
                const galleryItem = document.createElement('div');
                galleryItem.className = 'gallery-item';
                if (index === 0) galleryItem.classList.add('active');
                
                if (item.type === 'image') {
                  const img = document.createElement('img');
                  img.src = item.src;
                  img.alt = `${cottageName} - Image ${index + 1}`;
                  galleryItem.appendChild(img);
                } else if (item.type === 'video') {
                  const video = document.createElement('video');
                  video.src = item.src;
                  video.controls = true;
                  video.muted = true;
                  galleryItem.appendChild(video);
                }
                
                // Add click event to gallery items
                galleryItem.addEventListener('click', function() {
                  // Update main image
                  if (item.type === 'image') {
                    modalImage.src = item.src;
                  }
                  
                  // Update active state
                  document.querySelectorAll('.gallery-item').forEach(el => {
                    el.classList.remove('active');
                  });
                  this.classList.add('active');
                });
                
                galleryScroll.appendChild(galleryItem);
              });
            }
            
            // Show modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
          }
        });
      });

      // Close modal
      if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
          modal.style.display = 'none';
          document.body.style.overflow = 'auto';
        });
      }

      // Close modal when clicking outside
      window.addEventListener('click', function(event) {
        if (event.target === modal) {
          modal.style.display = 'none';
          document.body.style.overflow = 'auto';
        }
      });
    }

    // Initialize the page when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
      initRoomsPage();
      
      // Mobile menu functionality
      const menuBtn = document.getElementById('menuBtn');
      const closeMenuBtn = document.getElementById('closeMenu');
      const nav = document.getElementById('mainNav');
      
      if (menuBtn && nav) {
        menuBtn.addEventListener('click', function() {
          nav.classList.add('active');
        });
      }
      
      if (closeMenuBtn && nav) {
        closeMenuBtn.addEventListener('click', function() {
          nav.classList.remove('active');
        });
      }
    });
  


// ========== FEATURED COTTAGES FUNCTIONALITY ==========
function initFeaturedCottages() {
  const viewDetailsBtns = document.querySelectorAll('.view-details');
  
  viewDetailsBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const cottageName = this.getAttribute('data-cottage');
      // Redirect to rooms page or show modal
      window.location.href = 'rooms.html';
    });
  });
}

// ========== BOOKING PAGE FUNCTIONALITY ==========
function initBookingPage() {
  const bookingForm = document.getElementById('bookingForm');
  const checkinInput = document.getElementById('checkin');
  const checkoutInput = document.getElementById('checkout');
  const accommodationSelect = document.getElementById('accommodation');
  const adultsInput = document.getElementById('adults');
  const childrenInput = document.getElementById('children');

  // Set minimum date to today
  const today = new Date().toISOString().split('T')[0];
  if (checkinInput) checkinInput.min = today;
  if (checkoutInput) checkoutInput.min = today;

  // Update checkout min date when checkin changes
  if (checkinInput) {
    checkinInput.addEventListener('change', function() {
      if (this.value) {
        checkoutInput.min = this.value;
        if (checkoutInput.value && checkoutInput.value < this.value) {
          checkoutInput.value = '';
        }
      }
      calculatePrice();
    });
  }

  // Calculate price when any input changes
  [checkinInput, checkoutInput, accommodationSelect, adultsInput, childrenInput].forEach(element => {
    if (element) {
      element.addEventListener('change', calculatePrice);
    }
  });

  // Form submission
  if (bookingForm) {
    bookingForm.addEventListener('submit', function(e) {
      e.preventDefault();
      if (validateBookingForm()) {
        processBooking();
      }
    });
  }

  // Add Check Availability button
  addCheckAvailabilityButton();

  // Initial price calculation
  calculatePrice();
}

// ADD CHECK AVAILABILITY BUTTON
function addCheckAvailabilityButton() {
  const formActions = document.querySelector('.form-actions');
  if (formActions && !document.querySelector('.check-availability-btn')) {
    const checkAvailabilityBtn = document.createElement('button');
    checkAvailabilityBtn.type = 'button';
    checkAvailabilityBtn.className = 'btn secondary check-availability-btn';
    checkAvailabilityBtn.textContent = 'Check Availability';
    checkAvailabilityBtn.addEventListener('click', checkAvailability);
    
    formActions.insertBefore(checkAvailabilityBtn, formActions.firstChild);
  }
}

// CHECK AVAILABILITY FUNCTION
function checkAvailability() {
  const checkin = document.getElementById('checkin')?.value;
  const checkout = document.getElementById('checkout')?.value;
  const accommodation = document.getElementById('accommodation')?.value;

  if (!checkin || !checkout || !accommodation) {
    showMessage('Please fill in check-in, check-out dates and select accommodation first.', 'error');
    return;
  }

  // Simulate availability check (in real app, this would call an API)
  const isAvailable = Math.random() > 0.3; // 70% chance of availability for demo
  
  if (isAvailable) {
    showMessage('✅ This accommodation is available for your selected dates! You can proceed with booking.', 'success');
  } else {
    showMessage('❌ Sorry, this accommodation is not available for your selected dates. Please try different dates or another cottage.', 'error');
  }
}

function calculatePrice() {
  const checkin = document.getElementById('checkin')?.value;
  const checkout = document.getElementById('checkout')?.value;
  const accommodation = document.getElementById('accommodation');
  const priceBreakdown = document.getElementById('priceBreakdown');
  
  if (!checkin || !checkout || !accommodation?.value) {
    if (priceBreakdown) priceBreakdown.classList.remove('show');
    return;
  }

  // Calculate number of nights
  const checkinDate = new Date(checkin);
  const checkoutDate = new Date(checkout);
  const timeDiff = checkoutDate - checkinDate;
  const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
  
  if (nights <= 0) {
    if (priceBreakdown) priceBreakdown.classList.remove('show');
    return;
  }

  // Get accommodation price
  const selectedOption = accommodation.options[accommodation.selectedIndex];
  const pricePerNight = parseInt(selectedOption.getAttribute('data-price')) || 0;
  
  // Calculate total
  let total = pricePerNight * nights;
  
  // Update price breakdown
  if (document.getElementById('accommodationPrice')) {
    document.getElementById('accommodationPrice').textContent = `₱${pricePerNight.toLocaleString()}`;
    document.getElementById('nightsCount').textContent = `${nights} night${nights > 1 ? 's' : ''}`;
    document.getElementById('totalAmount').textContent = `₱${total.toLocaleString()}`;
    
    if (priceBreakdown) priceBreakdown.classList.add('show');
  }
}

function validateBookingForm() {
  const checkin = document.getElementById('checkin')?.value;
  const checkout = document.getElementById('checkout')?.value;
  const accommodation = document.getElementById('accommodation')?.value;
  const adults = document.getElementById('adults')?.value;
  const name = document.getElementById('name')?.value;
  const email = document.getElementById('email')?.value;
  const phone = document.getElementById('phone')?.value;
  const paymentMethod = document.getElementById('paymentMethod')?.value;

  if (!checkin || !checkout || !accommodation || !adults || !name || !email || !phone || !paymentMethod) {
    showMessage('Please fill in all required fields.', 'error');
    return false;
  }

  const checkinDate = new Date(checkin);
  const checkoutDate = new Date(checkout);
  
  if (checkoutDate <= checkinDate) {
    showMessage('Check-out date must be after check-in date.', 'error');
    return false;
  }

  // Validate GCash payment details
  if (paymentMethod === 'pay-now') {
    const gcashName = document.getElementById('gcashName')?.value;
    const gcashNumber = document.getElementById('gcashNumber')?.value;
    const paymentReference = document.getElementById('paymentReference')?.value;
    const paymentDate = document.getElementById('paymentDate')?.value;
    const receiptFile = document.getElementById('receiptUpload')?.files[0];

    if (!gcashName || !gcashNumber || !paymentReference || !paymentDate || !receiptFile) {
      showMessage('Please fill in all GCash payment details and upload receipt.', 'error');
      return false;
    }
  }

  return true;
}

// ========== NEW PAYMENT SYSTEM ==========
function togglePaymentOption() {
  const paymentMethod = document.getElementById('paymentMethod').value;
  const qrSection = document.getElementById('qrSection');
  const totalAmount = calculateTotalAmount();
  
  if (paymentMethod === 'pay-now') {
    if (qrSection) qrSection.style.display = 'block';
    if (document.getElementById('qrAmount')) {
      document.getElementById('qrAmount').textContent = `₱${totalAmount.toLocaleString()}`;
    }
    if (document.getElementById('qrReference')) {
      document.getElementById('qrReference').textContent = 'RESORT' + Date.now().toString().slice(-6);
    }
  } else {
    if (qrSection) qrSection.style.display = 'none';
  }
}

function processBooking() {
  // Validate form first
  if (!validateBookingForm()) return;

  const paymentMethod = document.getElementById('paymentMethod').value;
  
  if (!paymentMethod) {
    showMessage('Please select payment method.', 'error');
    return;
  }

  const formData = {
    name: document.getElementById('name').value,
    email: document.getElementById('email').value,
    phone: document.getElementById('phone').value,
    accommodation: document.getElementById('accommodation').value,
    checkin: document.getElementById('checkin').value,
    checkout: document.getElementById('checkout').value,
    adults: document.getElementById('adults').value,
    children: document.getElementById('children').value,
    paymentMethod: paymentMethod,
    timestamp: new Date().toISOString(),
    bookingId: 'RESORT' + Date.now().toString().slice(-6),
    amount: calculateTotalAmount(),
    paymentStatus: paymentMethod === 'pay-now' ? 'pending' : 'face-to-face'
  };

  // Handle GCash payment details
  if (paymentMethod === 'pay-now') {
    const receiptFile = document.getElementById('receiptUpload')?.files[0];
    if (!receiptFile) {
      showMessage('Please upload your GCash receipt screenshot.', 'error');
      return;
    }
    
    formData.receiptFile = receiptFile.name;
    formData.paymentDetails = {
      gcashName: document.getElementById('gcashName').value,
      gcashNumber: document.getElementById('gcashNumber').value,
      paymentReference: document.getElementById('paymentReference').value,
      paymentDate: document.getElementById('paymentDate').value,
      bookingReference: formData.bookingId
    };
  }

  // Save to localStorage
  const bookings = JSON.parse(localStorage.getItem('bookings') || '[]');
  bookings.push(formData);
  localStorage.setItem('bookings', JSON.stringify(bookings));

  // Show success message
  showBookingSuccess(formData);
}

function calculateTotalAmount() {
  const accommodation = document.getElementById('accommodation');
  if (!accommodation) return 0;
  
  const selectedOption = accommodation.options[accommodation.selectedIndex];
  const pricePerNight = parseInt(selectedOption.getAttribute('data-price')) || 0;
  
  const checkin = document.getElementById('checkin')?.value;
  const checkout = document.getElementById('checkout')?.value;
  
  if (!checkin || !checkout) return pricePerNight;
  
  const checkinDate = new Date(checkin);
  const checkoutDate = new Date(checkout);
  const timeDiff = checkoutDate - checkinDate;
  const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
  
  return pricePerNight * nights;
}

function formatCottageName(cottageValue) {
  const cottageNames = {
    'white-house': 'White House',
    'penthouse': 'Penthouse',
    'aqua-class': 'Aqua Class',
    'heartsuite': 'Heartsuite',
    'stephs-skylounge': 'Steph\'s Skylounge',
    'stephs-848': 'Steph\'s 848',
    'stephs-846': 'Steph\'s 846',
    'concierge-817': 'Concierge 817',
    'de-luxe': 'De Luxe',
    'concierge-815-819': 'Concierge 815/819',
    'premium-840': 'Premium 840',
    'beatrice-a': 'Beatrice A',
    'premium-838': 'Premium 838',
    'giant-kubo': 'Giant Kubo',
    'seaside-whole': 'Seaside (Whole)',
    'beatrice-b': 'Beatrice B',
    'seaside-half': 'Seaside (Half)',
    'bamboo-kubo': 'Bamboo Kubo',
  };
  
  return cottageNames[cottageValue] || cottageValue;
}

function showBookingSuccess(bookingData) {
  let paymentInfoHTML = '';
  
  if (bookingData.paymentMethod === 'pay-now' && bookingData.paymentDetails) {
    paymentInfoHTML = `
      <div class="payment-confirmation">
        <h4>Payment Details Received</h4>
        <div class="payment-details">
          <p><strong>GCash Name:</strong> ${bookingData.paymentDetails.gcashName}</p>
          <p><strong>GCash Number:</strong> ${bookingData.paymentDetails.gcashNumber}</p>
          <p><strong>Payment Reference:</strong> ${bookingData.paymentDetails.paymentReference}</p>
          <p><strong>Payment Date:</strong> ${new Date(bookingData.paymentDetails.paymentDate).toLocaleString()}</p>
          <p><strong>Booking Reference:</strong> ${bookingData.paymentDetails.bookingReference}</p>
        </div>
        <p>We will verify your payment and please check your Email for confirmation. Thank you.</p>
      </div>
    `;
  }

  const successHTML = `
    <div class="booking-success" id="bookingSuccess">
      <div class="success-icon">✅</div>
      <h3>Booking Submitted Successfully!</h3>
      
      <div class="booking-details">
        <p><strong>Reference Number:</strong> ${bookingData.bookingId}</p>
        <p><strong>Name:</strong> ${bookingData.name}</p>
        <p><strong>Cottage:</strong> ${formatCottageName(bookingData.accommodation)}</p>
        <p><strong>Check-in:</strong> ${bookingData.checkin}</p>
        <p><strong>Check-out:</strong> ${bookingData.checkout}</p>
        <p><strong>Total Amount:</strong> ₱${bookingData.amount.toLocaleString()}</p>
      </div>

      ${paymentInfoHTML}

      ${bookingData.paymentMethod === 'face-to-face' ? `
        <div class="next-steps">
          <h4>Next Steps for Face-to-Face Payment:</h4>
          <p>Please pay upon check-in. Your booking is reserved for 24 hours.</p>
          <p><strong>Bring your reference number:</strong> ${bookingData.bookingId}</p>
        </div>
      ` : ''}

      <div class="action-buttons">

        <a href="index.html" class="btn secondary">Back to Home</a>
      </div>
    </div>
  `;

  // Replace form with success message
  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    bookingForm.innerHTML = successHTML;
    
    // AUTO-SCROLL TO TOP after a short delay
    setTimeout(() => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
      
      // Optional: Highlight the success message
      const successElement = document.getElementById('bookingSuccess');
      if (successElement) {
        successElement.style.animation = 'pulse 2s ease-in-out';
      }
    }, 300);
  }
}
// ========== CONTACT FORM FUNCTIONALITY ==========
function initContactForm() {
  const contactForm = document.getElementById('contactForm');
  
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        subject: document.getElementById('subject').value,
        message: document.getElementById('message').value,
        timestamp: new Date().toISOString()
      };
      
      const contacts = JSON.parse(localStorage.getItem('contactMessages') || '[]');
      contacts.push(formData);
      localStorage.setItem('contactMessages', JSON.stringify(contacts));
      
      showMessage('✅ Thank you for your message! We\'ll get back to you within 24 hours.', 'success');
      contactForm.reset();
    });
  }
}

// ========== FAQ FUNCTIONALITY ==========
function initFAQ() {
  const faqItems = document.querySelectorAll('.faq-item');
  const categoryBtns = document.querySelectorAll('.category-btn');
  
  // FAQ toggle functionality
  faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');
    
    question.addEventListener('click', () => {
      // Close other open items
      faqItems.forEach(otherItem => {
        if (otherItem !== item && otherItem.classList.contains('active')) {
          otherItem.classList.remove('active');
        }
      });
      
      // Toggle current item
      item.classList.toggle('active');
    });
  });
  
  // Category filter functionality
  categoryBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const category = btn.getAttribute('data-category');
      
      // Update active button
      categoryBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      
      // Filter FAQ items
      faqItems.forEach(item => {
        if (category === 'all' || item.getAttribute('data-category') === category) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
}

// ========== FOOTER LINKS - COMPLETELY FIXED ==========
function initFooterLinks() {
  console.log('Initializing footer links...');
  
  // Remove any problematic links and replace with working ones
  const footerLinks = document.querySelectorAll('.footer-col a');
  
  footerLinks.forEach(link => {
    const href = link.getAttribute('href');
    
    // Fix broken payment, cancellation, terms links
    if (href === 'payment.html') {
      link.setAttribute('href', 'faq.html#payment');
      console.log('Fixed payment link');
    }
    else if (href === 'cancellation.html') {
      link.setAttribute('href', 'faq.html#cancellation');
      console.log('Fixed cancellation link');
    }
    else if (href === 'terms.html') {
      link.setAttribute('href', 'faq.html#terms');
      console.log('Fixed terms link');
    }
  });
  
  // Handle FAQ anchor links with proper navigation
  const faqAnchorLinks = document.querySelectorAll('a[href*="faq.html#"]');
  
  faqAnchorLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      console.log('FAQ anchor link clicked:', href);
      
      // Allow default navigation to happen normally
      // The browser will handle the FAQ page loading and anchor scrolling
    });
  });
  
  // Handle page load with anchor - IMPROVED
  if (window.location.hash && window.location.pathname.includes('faq.html')) {
    setTimeout(() => {
      const targetId = window.location.hash.substring(1);
      const targetElement = document.getElementById(targetId);
      
      if (targetElement) {
        console.log('Scrolling to target:', targetId);
        
        // More reliable scrolling with offset for header
        const headerHeight = 80;
        const elementPosition = targetElement.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerHeight;
        
        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
        
        // Highlight the section
        targetElement.style.backgroundColor = 'rgba(10, 132, 255, 0.1)';
        setTimeout(() => {
          targetElement.style.backgroundColor = '';
        }, 3000);
      }
    }, 1000);
  }
  
  console.log('Footer links completely fixed and ready');
}

// ========== UTILITY FUNCTIONS ==========
function showMessage(message, type = 'success') {
  // Remove existing messages
  const existingMessage = document.querySelector('.success-message, .error-message');
  if (existingMessage) {
    existingMessage.remove();
  }

  const messageDiv = document.createElement('div');
  messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
  messageDiv.textContent = message;
  messageDiv.style.cssText = `
    padding: 1rem;
    margin: 1rem 0;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    ${type === 'success' ? 
      'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 
      'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'
    }
  `;
  
  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    bookingForm.insertBefore(messageDiv, bookingForm.firstChild);
  }

  setTimeout(() => {
    messageDiv.remove();
  }, 5000);
}

// Handle payment success from GCash window
window.addEventListener('message', function(event) {
  if (event.data && event.data.payment === 'success') {
    showMessage('🎉 Payment successful! Your booking is confirmed. We\'ve sent a confirmation email.', 'success');
    
    const bookings = JSON.parse(localStorage.getItem('bookings') || '[]');
    const lastBooking = bookings[bookings.length - 1];
    if (lastBooking) {
      lastBooking.paymentStatus = 'paid';
      lastBooking.transactionId = event.data.transactionId;
      localStorage.setItem('bookings', JSON.stringify(bookings));
    }
    
    setTimeout(() => {
      alert(`🏝️ Booking Confirmed!\\n\\nName: ${event.data.booking.name}\\nAmount: ₱${event.data.amount.toLocaleString()}\\nReference: ${event.data.booking.bookingId}\\n\\nThank you for choosing Heart Of D' Ocean!`);
    }, 1000);
  }
});

// ========== PAGE-SPECIFIC INITIALIZATION ==========
function initPageSpecificFeatures() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  
  console.log('Current page:', currentPage);
  
  if (currentPage === 'booking.html') {
    initBookingPage();
  }
  
  if (currentPage === 'gallery.html') {
    initLightbox();
  }
  
  if (currentPage === 'rooms.html') {
    initRoomsPage();
  }
  
  if (currentPage === 'index.html') {
    initFeaturedCottages();
  }
  
  if (currentPage === 'contact.html') {
    initContactForm();
  }
  
  if (currentPage === 'faq.html') {
    initFAQ();
  }
  
  // Initialize footer links on all pages
  initFooterLinks();
}

// ========== INITIALIZE EVERYTHING ==========
document.addEventListener('DOMContentLoaded', function() {
  initLoadingAnimation();
  initMobileMenu();
  initDarkMode();
  setActivePage();
  initScrollEffect();
  initPageSpecificFeatures();
  
  console.log('❤️ Heart Of D\' Ocean Beach Resort website loaded successfully!');
});