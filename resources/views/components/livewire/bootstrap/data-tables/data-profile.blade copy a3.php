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

    <!-- Desktop Tabs -->
    <ul class="nav nav-tabs mb-3" id="profileTab" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
          data-bs-target="#overview">Overview</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#job">Job &
          Compensation</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">Documents</button>
      </li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity">Activity
          Logs</button></li>
    </ul>

    <div class="tab-content" id="profileTabContent">
      <!-- Overview -->
      <div class="tab-pane fade show active" id="overview">
        <div class="card p-3 mb-3">
          <h5>Personal Information</h5>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Email:</strong> jane.doe@example.com</p>
              <p><strong>Phone:</strong> +1 234 567 890</p>
            </div>
            <div class="col-md-6">
              <p><strong>Address:</strong> 123 Main St, San Francisco, CA</p>
              <p><strong>Date of Birth:</strong> 12 Jan 1990</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Job -->
      <div class="tab-pane fade" id="job">
        <div class="card p-3 mb-3">
          <h5>Job Details</h5>
          <p><strong>Title:</strong> Software Engineer</p>
          <p><strong>Department:</strong> Product</p>
          <p><strong>Manager:</strong> John Smith</p>
          <p><strong>Date Joined:</strong> 1 Mar 2022</p>
        </div>
        <div class="card p-3">
          <h5>Compensation</h5>
          <p><strong>Salary:</strong> $80,000 / year</p>
          <p><strong>Allowances:</strong> $500 / month</p>
          <p><strong>Deductions:</strong> $200 / month</p>
        </div>
      </div>

      <!-- Documents -->
      <div class="tab-pane fade" id="documents">
        <div class="card p-3">
          <h5>Documents</h5>
          <table class="table">
            <thead>
              <tr>
                <th>Document Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Uploaded</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Employment Contract</td>
                <td>PDF</td>
                <td><span class="badge bg-success">Approved</span></td>
                <td>2025-01-10</td>
              </tr>
              <tr>
                <td>ID Card</td>
                <td>Image</td>
                <td><span class="badge bg-warning">Pending</span></td>
                <td>2025-01-15</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Activity -->
      <div class="tab-pane fade" id="activity">
        <div class="card p-3">
          <h5>Recent Activity</h5>
          <ul class="list-group list-group-flush">
            <li class="list-group-item">Logged in at 9:00 AM, 22 Sep 2025</li>
            <li class="list-group-item">Updated bank details on 20 Sep 2025</li>
            <li class="list-group-item">Uploaded tax form on 15 Sep 2025</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Mobile Accordion -->
    <div class="accordion" id="profileAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseOverview">Overview</button></h2>
        <div id="collapseOverview" class="accordion-collapse collapse show">
          <div class="accordion-body">
            <p><strong>Email:</strong> jane.doe@example.com</p>
            <p><strong>Phone:</strong> +1 234 567 890</p>
            <p><strong>Address:</strong> 123 Main St, San Francisco, CA</p>
            <p><strong>Date of Birth:</strong> 12 Jan 1990</p>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseJob">Job & Compensation</button></h2>
        <div id="collapseJob" class="accordion-collapse collapse">
          <div class="accordion-body">
            <p><strong>Title:</strong> Software Engineer</p>
            <p><strong>Department:</strong> Product</p>
            <p><strong>Manager:</strong> John Smith</p>
            <p><strong>Salary:</strong> $80,000 / year</p>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseDocuments">Documents</button></h2>
        <div id="collapseDocuments" class="accordion-collapse collapse">
          <div class="accordion-body">
            <ul>
              <li>Employment Contract – Approved</li>
              <li>ID Card – Pending</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseActivity">Activity Logs</button></h2>
        <div id="collapseActivity" class="accordion-collapse collapse">
          <div class="accordion-body">
            <ul>
              <li>Logged in at 9:00 AM, 22 Sep 2025</li>
              <li>Updated bank details on 20 Sep 2025</li>
              <li>Uploaded tax form on 15 Sep 2025</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

  </div>


</div>