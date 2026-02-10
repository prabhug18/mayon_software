
    const ctx = document.getElementById('salesChart');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'apr', 'may', 'may'],
        datasets: [{
          label: 'Sales',
          data: [150, 200, 170, 190, 200, 230],
          backgroundColor: '#002a70'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  
    const profileToggleBtn = document.getElementById('profileToggleBtn');
    const profileCard = document.getElementById('profileCard');

    profileToggleBtn.addEventListener('click', () => {
      profileCard.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
      if (!profileCard.contains(e.target) && !profileToggleBtn.contains(e.target)) {
        profileCard.classList.remove('show');
      }
    });

    const quickLinkBtn = document.getElementById("quickLinkBtn");
    const quickLinkPopup = document.getElementById("quickLinkPopup");

    quickLinkBtn.addEventListener("click", function () {
      quickLinkPopup.style.display =
        quickLinkPopup.style.display === "block" ? "none" : "block";
    });

    // Optional: Close on outside click
    window.addEventListener("click", function (e) {
      if (!quickLinkBtn.contains(e.target) && !quickLinkPopup.contains(e.target)) {
        quickLinkPopup.style.display = "none";
      }
    });
