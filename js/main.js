// Main JavaScript file for Agride
// All interactivity and AJAX will be handled here

document.addEventListener('DOMContentLoaded', () => {
  const ridesContainer = document.getElementById('ridesList') || document.getElementById('ridesContainer');
  const searchBtn = document.getElementById('searchBtn');
  const searchRidesForm = document.getElementById('searchRidesForm');
  const offerRideForm = document.getElementById('offerRideForm');

  // Section fade-in effect
  document.querySelectorAll('.fade-in-section').forEach(section => {
    section.style.opacity = 0;
    section.style.transform = 'translateY(30px)';
    setTimeout(() => {
      section.style.transition = 'opacity 1s, transform 1s';
      section.style.opacity = 1;
      section.style.transform = 'none';
    }, 200);
  });

  // Function to fetch rides and display them
  const loadRides = (params = {}, limit = undefined) => {
    if (!ridesContainer) return; // Only run on rides.html or index.html

    // Show loading spinner while fetching rides
    ridesContainer.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="height:120px"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    // Construct query string from params object
    const queryString = new URLSearchParams(params).toString();
    const url = `php/get_rides.php${queryString ? '?' + queryString : ''}`;

    fetch(url)
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
      })
      .then(rides => {
        ridesContainer.innerHTML = '';
        if (rides.length === 0) {
          ridesContainer.innerHTML = '<div class="alert alert-info">No rides available at the moment.</div>';
          return;
        }
        // Limit rides if limit is set
        const ridesToShow = limit ? rides.slice(0, limit) : rides;
        ridesToShow.forEach((ride, index) => {
          const col = document.createElement('div');
          col.className = 'col-md-6 col-lg-3 mb-4'; // Add mb-4 for spacing
          const cardImg = document.createElement('img');
          // Ride image (fallback if missing)
          let image = ride.image || '';
          const rideImages = ['img/ride1.jpg', 'img/ride2.jpg', 'img/ride3.jpg', 'img/ride4.jpg', 'img/ride5.jpg', 'img/ride6.jpg', 'img/ride8.jpg'];
          if (!image || image === 'img/default_ride.jpg') {
            // Cycle through available images, but offset by index to avoid repetition
            image = rideImages[(index) % rideImages.length];
          }
          cardImg.src = image;
          cardImg.alt = `Ride from ${ride.from_city} to ${ride.to_city}`;
          cardImg.onerror = function() {
            // If image fails to load, use a fallback, always pick a different one
            let fallbackIdx = (index + 3) % rideImages.length;
            if (rideImages[fallbackIdx] === this.src.split('/').pop()) fallbackIdx = (fallbackIdx + 1) % rideImages.length;
            this.src = rideImages[fallbackIdx];
          };
          cardImg.className = 'card-img-top';
          const cardBody = document.createElement('div');
          cardBody.className = 'card-body';
          cardBody.innerHTML = `
            <h5 class="card-title">${ride.from_city} → ${ride.to_city}</h5>
            <p class="card-text mb-1"><strong>Date:</strong> ${ride.ride_date}</p>
            <p class="card-text mb-1"><strong>Capacity:</strong> ${ride.capacity} tons</p>
            <p class="card-text mb-2"><strong>Vehicle:</strong> ${ride.vehicle_type || 'N/A'}</p>
            <a href="ride.html?id=${ride.id}" class="btn btn-primary">View Details</a>
          `;
          const card = document.createElement('div');
          card.className = 'card ride-card h-100 shadow-sm border-0';
          card.appendChild(cardImg);
          card.appendChild(cardBody);
          col.appendChild(card);
          ridesContainer.appendChild(col);
        });
        // If more rides exist, show "See More" link
        if (limit && rides.length > limit) {
          const moreCol = document.createElement('div');
          moreCol.className = 'col-12 text-center';
          moreCol.innerHTML = '<a href="rides.html" class="btn btn-outline-success mt-3">See More Rides</a>';
          ridesContainer.appendChild(moreCol);
        }
      })
      .catch(error => {
        console.error('Error fetching rides:', error);
        ridesContainer.innerHTML = '<div class="alert alert-danger">Error loading rides. Please try again later.</div>';
      });
  };

  // Initial load of rides on rides.html or index.html
  if (ridesContainer) {
    // Show only 4 rides on index.html, all on rides.html
    if (window.location.pathname.endsWith('index.html') || window.location.pathname === '/' || window.location.pathname === '/Agriride/' || window.location.pathname === '/Agriride/index.html') {
      loadRides({}, 4);
    } else {
      loadRides();
    }
  }

  // Search form AJAX handler
  if (searchRidesForm && searchBtn) {
    searchRidesForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const fromCity = document.getElementById('fromCity').value;
      const toCity = document.getElementById('toCity').value;
      const rideDate = document.getElementById('rideDate').value;
      const params = {};
      if (fromCity) params.from_city = fromCity;
      if (toCity) params.to_city = toCity;
      if (rideDate) params.ride_date = rideDate;
      loadRides(params);
    });
  }

  // Function to update Navbar based on login status
  const updateNavbar = () => {
    // Find the nav links container (ul.navbar-nav)
    const navLinksContainer = document.querySelector('nav .navbar-nav');
    if (!navLinksContainer) return;

    fetch('php/check_session.php')
      .then(response => response.json())
      .then(session => {
        // Remove all auth-related links first
        navLinksContainer.querySelectorAll('.auth-link, .profile-dropdown').forEach(link => link.remove());

        if (session.logged_in) {
          // Profile dropdown
          const profileDropdown = document.createElement('li');
          profileDropdown.className = 'nav-item dropdown profile-dropdown';
          profileDropdown.innerHTML = `
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="img/ride1.jpg" alt="Profile" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">
              ${session.user_name || 'Profile'}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
              <li><a class="dropdown-item" href="profile.html">Profile</a></li>
              <li><a class="dropdown-item" href="php/logout.php">Logout</a></li>
            </ul>
          `;
          navLinksContainer.appendChild(profileDropdown);
        } else {
          // Login and Register links
          const loginLi = document.createElement('li');
          loginLi.className = 'nav-item auth-link';
          loginLi.innerHTML = '<a class="nav-link" href="login.html">Login</a>';

          const registerLi = document.createElement('li');
          registerLi.className = 'nav-item auth-link';
          registerLi.innerHTML = '<a class="nav-link" href="register.html">Register</a>';

          navLinksContainer.appendChild(loginLi);
          navLinksContainer.appendChild(registerLi);
        }
      })
      .catch(error => {
        console.error('Error checking session:', error);
      });
  };

  updateNavbar();

  if (offerRideForm) {
    offerRideForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(offerRideForm);
      fetch('php/add_ride.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        alert(data.message);
        if (data.success) {
          offerRideForm.reset();
          window.location.href = 'rides.html';
        }
      })
      .catch(error => {
        console.error('Error submitting form:', error);
        alert('An error occurred. Please try again.');
      });
    });
  }
});
