// ride.js: Fetch and display ride details based on ride ID in URL

document.addEventListener('DOMContentLoaded', () => {
  const rideDetailsDiv = document.getElementById('rideDetails');
  const loadingSpinner = document.getElementById('loadingSpinner');

  // Helper to get URL parameter
  function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }

  const rideId = getQueryParam('id');
  if (!rideId) {
    loadingSpinner.style.display = 'none';
    rideDetailsDiv.innerHTML += '<div class="alert alert-danger">No ride ID provided.</div>';
    return;
  }

  fetch(`php/get_ride.php?id=${encodeURIComponent(rideId)}`)
    .then(response => response.json())
    .then(ride => {
      loadingSpinner.style.display = 'none';
      if (!ride || ride.error) {
        rideDetailsDiv.innerHTML += `<div class="alert alert-danger">${ride.error || 'Ride not found.'}</div>`;
        return;
      }
      rideDetailsDiv.innerHTML = `
        <div class="row g-4">
          <div class="col-md-7 offset-md-2">
            <h2 class="mb-3 text-success fw-bold text-center">${ride.from_city} &rarr; ${ride.to_city}</h2>
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Date:</strong> ${ride.ride_date || 'N/A'}</li>
              <li class="list-group-item"><strong>Capacity:</strong> ${ride.capacity || 'N/A'}</li>
              <li class="list-group-item"><strong>Vehicle Type:</strong> ${ride.vehicle_type || 'N/A'}</li>
              <li class="list-group-item"><strong>Driver:</strong> ${ride.driver_name || 'N/A'}</li>
              <li class="list-group-item"><strong>Contact:</strong> ${ride.driver_contact || 'N/A'}</li>
            </ul>
            <p class="mb-3"><strong>Additional Info:</strong><br>${ride.description || 'No additional information provided.'}</p>
            <a href="rides.html" class="btn btn-outline-success">Back to Rides</a>
            <button id="bookRideBtn" class="btn btn-success ms-2">Book Ride</button>
          </div>
        </div>
      `;
      // Add event listener for booking
      const bookBtn = document.getElementById('bookRideBtn');
      if (bookBtn) {
        bookBtn.onclick = function() {
          fetch('php/book_ride.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ride_id=${encodeURIComponent(ride.id)}`
          })
          .then(res => res.json())
          .then(data => {
            alert(data.message);
            // Optionally refresh or update UI
          })
          .catch(() => alert('Error booking ride.'));
        };
      }
    })
    .catch(error => {
      loadingSpinner.style.display = 'none';
      rideDetailsDiv.innerHTML += '<div class="alert alert-danger">Error loading ride details. Please try again later.</div>';
      console.error('Error fetching ride details:', error);
    });
});
