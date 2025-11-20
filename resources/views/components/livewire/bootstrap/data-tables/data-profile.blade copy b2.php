<div>
  <style>
    .profile-header {
      background: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      padding: 2rem 1rem;
    }
    .profile-photo {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .card {
      border-radius: 0.75rem;
    }
    .section-title {
      font-weight: 600;
      margin-bottom: 0.75rem;
    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<div class="container-fluid">

<!-- Sticky Employee Header -->
<div class="sticky-top bg-white border-bottom p-2 shadow-sm" style="z-index:1030;">
  <div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <img src="https://via.placeholder.com/60" alt="User Photo" class="rounded-circle me-2">
      <div>
        <h6 class="mb-0">Jane Doe</h6>
        <small class="text-muted">Software Engineer • EMP12345</small>
      </div>
    </div>
    <!-- Quick Actions -->
    <div>
      <button class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil-square"></i></button>
      <button class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-chat-dots"></i></button>
      <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-pdf"></i></button>
    </div>
  </div>

  <!-- Sticky Tabs -->
  <ul class="nav nav-tabs mt-2" id="profileTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">Profile</button></li>
    <li class="nav-item"><button class="nav-link" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button">Job</button></li>
    <li class="nav-item"><button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button">Documents</button></li>
    <li class="nav-item"><button class="nav-link" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll" type="button">Payroll</button></li>
    <li class="nav-item"><button class="nav-link" id="benefits-tab" data-bs-toggle="tab" data-bs-target="#benefits" type="button">Benefits</button></li>
    <li class="nav-item"><button class="nav-link" id="timeoff-tab" data-bs-toggle="tab" data-bs-target="#timeoff" type="button">Time Off</button></li>
  </ul>
</div>



  <!-- Tab Content -->
  <div class="tab-content mt-3" id="profileTabsContent">

    <!-- Profile -->
    <div class="tab-pane fade show active" id="profile" role="tabpanel">
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title">About</h6>
          <p>John is a highly skilled software engineer with over 8 years of experience in backend development, cloud infrastructure, and mentoring junior developers.</p>
        </div>
      </div>
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title">Contact Information</h6>
          <p><strong>Email:</strong> john.doe@email.com</p>
          <p><strong>Phone:</strong> +234 801 234 5678</p>
          <p><strong>Address:</strong> 123 Ikoyi Rd, Lagos</p>
        </div>
      </div>
    </div>

    <!-- Job -->
    <div class="tab-pane fade" id="job" role="tabpanel">
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title">Job Information</h6>
          <p><strong>Department:</strong> Engineering</p>
          <p><strong>Manager:</strong> Sarah Johnson</p>
          <p><strong>Hire Date:</strong> Jan 15, 2021</p>
          <p><strong>Employment Type:</strong> Full-Time</p>
          <p><strong>Status:</strong> Active</p>
        </div>
      </div>
    </div>

    <!-- Documents -->
    <div class="tab-pane fade" id="documents" role="tabpanel">
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title">Employee Documents</h6>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              Employment Contract
              <a href="#" class="btn btn-sm btn-outline-primary">View</a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              ID Card
              <a href="#" class="btn btn-sm btn-outline-primary">View</a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              Performance Review
              <a href="#" class="btn btn-sm btn-outline-primary">Download</a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Payroll -->
    <div class="tab-pane fade" id="payroll" role="tabpanel">
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title">Payroll Information</h6>
          <p><strong>Salary:</strong> $75,000 / year</p>
          <p><strong>Bank:</strong> Admin Bank</p>
          <p><strong>Account No:</strong> **** 4567</p>
          <p><strong>Last Payment:</strong> Aug 31, 2025</p>
        </div>
      </div>
    </div>

    <!-- Benefits -->
    <div class="tab-pane fade" id="benefits" role="tabpanel">
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title">Benefits</h6>
          <ul>
            <li>Health Insurance (HMO)</li>
            <li>Retirement Plan</li>
            <li>Gym Membership</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Time Off -->
    <div class="tab-pane fade" id="timeoff" role="tabpanel">
      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title">Time Off</h6>
          <p><strong>Vacation Days Available:</strong> 12</p>
          <p><strong>Sick Days Used:</strong> 3</p>
          <button class="btn btn-primary btn-sm">Request Time Off</button>
        </div>
      </div>
    </div>

  </div>
</div>

</div>
