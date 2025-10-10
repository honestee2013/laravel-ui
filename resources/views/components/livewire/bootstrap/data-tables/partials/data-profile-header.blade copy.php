<div>
  <style>
    body {
      background-color: #f8f9fa;
    }

    .profile-header {
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      color: white;
      border-radius: .75rem;
      padding: 2rem;
      margin-bottom: 1rem;
    }

    .profile-header img {
      border: 4px solid white;
      border-radius: 50%;
      width: 100px;
      height: 100px;
      object-fit: cover;
    }

    .nav-tabs .nav-link.active {
      background-color: #fff;
      border-color: #dee2e6 #dee2e6 #fff;
    }

    .card {
      border-radius: .75rem;
    }

    /* Hide tabs on small screens, show accordion */
    @media (max-width: 768px) {
      #profileTab {
        display: none;
      }

      #profileAccordion {
        display: block !important;
      }
    }

    @media (min-width: 769px) {
      #profileAccordion {
        display: none;
      }
    }




    /* Compact sticky bar for mobile */
    .profile-header {
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      color: white;
      border-radius: .75rem;
      padding: 2rem;
      margin-bottom: 1rem;
    }

    .profile-header img {
      border: 4px solid white;
      border-radius: 50%;
      width: 100px;
      height: 100px;
      object-fit: cover;
    }

    /* Compact sticky version (only mobile) */
    .profile-header-sticky {
      display: none;
      position: sticky;
      top: 0;
      z-index: 1050;
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      color: white;
      padding: .5rem 1rem;
      font-size: .9rem;
    }

    .profile-header-sticky img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      margin-right: .5rem;
    }

    /* Switch versions depending on screen size */
    @media (max-width: 768px) {
      .profile-header {
        display: none;
      }

      .profile-header-sticky {
        display: flex;
        align-items: center;
      }
    }
  </style>


  <div class="container my-4">
    <!-- Full Profile Header (Desktop) -->
    <div class="profile-header d-flex align-items-center">
      <img src="https://via.placeholder.com/100" alt="User Photo" class="me-3">
      <div>
        <h3 class="mb-0">Jane Doe</h3>
        <p class="mb-0">Software Engineer • Product Team</p>
        <small>Employee ID: EMP12345</small>
      </div>
    </div>

    <!-- Sticky Compact Header (Mobile with Collapsible Card) -->
    <div class="profile-header-sticky justify-content-between" data-bs-toggle="collapse"
      data-bs-target="#miniProfileCard" aria-expanded="false">
      <div class="d-flex align-items-center">
        <img src="https://via.placeholder.com/100" alt="User Photo">
        <div>
          <strong>Jane Doe</strong><br>
          <small>Software Engineer</small>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="d-flex align-items-center">
        <button class="btn btn-sm btn-light me-1" title="Edit Profile">
          <i class="bi bi-pencil-square"></i>
        </button>
        <button class="btn btn-sm btn-light me-1" title="Message">
          <i class="bi bi-chat-dots"></i>
        </button>
        <button class="btn btn-sm btn-light" title="Download PDF">
          <i class="bi bi-file-earmark-pdf"></i>
        </button>
      </div>
    </div>

    <!-- Collapsible Expanded Profile Info -->
    <div class="collapse mt-2" id="miniProfileCard">
      <div class="card card-body p-2">
        <p class="mb-1"><i class="bi bi-envelope me-2"></i> jane.doe@example.com</p>
        <p class="mb-1"><i class="bi bi-telephone me-2"></i> +1 234 567 890</p>
        <p class="mb-0"><i class="bi bi-person-badge me-2"></i> Employee ID: EMP12345</p>
      </div>
    </div>

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">



  </div>


</div>