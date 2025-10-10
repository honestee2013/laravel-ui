<div class="row g-4">
    @if (!empty($config['switchViews']) && isset($config['switchViews']['profile']))
        @include('qf::components.livewire.bootstrap.widgets.spinner')



        @php
            $employee = null;
        @endphp



        <!-- Include Bootstrap Icons only if not already in app.php -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

        <style>
            .responsive-tab-nav {
                /* Desktop: uses your app's existing nav-tabs styling */
            }

            @media (max-width: 767.98px) {
                .responsive-tab-nav {
                    display: flex;
                    flex-wrap: nowrap;
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    border-bottom: none !important;
                    padding: 0.5rem 0;
                    gap: 0.5rem;
                    margin-bottom: 1rem;
                    min-height: 44px;
                    align-items: center;
                    scrollbar-width: none;
                }

                .responsive-tab-nav::-webkit-scrollbar {
                    display: none;
                }

                .responsive-tab-nav .nav-item {
                    flex: 0 0 auto;
                }

                /* Mobile pill buttons with visible borders */
                .responsive-tab-nav .nav-link {
                    white-space: nowrap;
                    padding: 0.4rem 0.75rem;
                    font-size: 0.875rem;
                    border-radius: 0.375rem;
                    touch-action: manipulation;
                    border: 1px solid var(--bs-primary, #0d6efd);
                    background: transparent;
                    color: var(--bs-primary, #0d6efd);
                }

                .responsive-tab-nav .nav-link.active {
                    background-color: var(--bs-primary, #0d6efd);
                    color: white;
                    border-color: var(--bs-primary, #0d6efd);
                }
            }





            /* ✅ Fixed: Mobile sticky profile header */
            @media (max-width: 767.98px) {

                :root {
                    --navbar-height: 56px;
                    --profile-header-height: 60px;
                }
                    
                .profile-sticky-wrapper {
                    position: sticky;
                    top: 56px;
                    /* ← Below fixed top navbar */
                    z-index: 1020;
                }

                /* Chevron rotation (already working) */
                [aria-expanded="true"] .chevron-rotate {
                    transform: rotate(180deg);
                    transition: transform 0.2s ease;
                }

                [aria-expanded="false"] .chevron-rotate {
                    transform: rotate(0deg);
                    transition: transform 0.2s ease;
                }


                /* Sticky tabs on mobile */
                .responsive-tab-nav {
                    position: sticky;
                    top: calc(var(--navbar-height) + var(--profile-header-height));
                    background: white;
                    z-index: 1010;
                    /* Keep your existing styles below */
                    display: flex;
                    flex-wrap: nowrap;
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    border-bottom: none !important;
                    padding: 0.5rem 0;
                    gap: 0.5rem;
                    margin-bottom: 1rem;
                    min-height: 44px;
                    align-items: center;
                    scrollbar-width: none;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);

                }
            }


            
        </style>

        <div class="container my-4">


            <!-- Full Profile Header (Desktop Only) -->
            <div class="profile-header d-none d-md-flex align-items-center mb-4"
                style="background: linear-gradient(135deg, var(--bs-primary, #4e73df), var(--bs-success, #1cc88a)); color: white; border-radius: 0.75rem; padding: 2rem;">
                <img src="{{ $employee?->photo_url ?? 'https://via.placeholder.com/100' }}"
                    alt="{{ $employee?->name }} Photo" class="me-3"
                    style="border: 4px solid white; border-radius: 50%; width: 100px; height: 100px; object-fit: cover;">
                <div>
                    <h3 class="mb-0">{{ $employee?->name }}</h3>
                    <p class="mb-0">{{ $employee?->job_title }} • {{ $employee?->department }}</p>
                    <small>Employee ID: {{ $employee?->employee_id }}</small>
                </div>
            </div>

            <!-- Sticky Compact Header (Mobile Only) -->
            
            <div class="d-md-none mb-3 profile-sticky-wrapper">
                <!-- ✅ Custom sticky wrapper -->
                    <div class="d-flex justify-content-between align-items-center"
                        style="background: linear-gradient(135deg, var(--bs-primary, #4e73df), var(--bs-success, #1cc88a)); color: white; padding: 0.75rem 1rem; border-radius: 0.5rem;"
                        data-bs-toggle="collapse" data-bs-target="#miniProfileCard" aria-expanded="false"
                        aria-controls="miniProfileCard" role="button">
                        <div class="d-flex align-items-center">
                            <img src="{{ $employee?->photo_url ?? 'https://via.placeholder.com/100' }}"
                                alt="{{ $employee?->name }} Photo"
                                style="width: 36px; height: 36px; border-radius: 50%; margin-right: 0.75rem; border: 2px solid white;">
                            <div>
                                <strong>{{ $employee?->name }}</strong><br>
                                <small>{{ $employee?->job_title }}</small>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-white ms-2 chevron-rotate"></i>
                    </div>


                <!-- Collapsible content -->
                <div class="collapse mt-2" id="miniProfileCard">
                    <div class="card card-body p-3">
                        <p class="mb-1"><i class="bi bi-envelope me-2"></i> {{ $employee?->email }}</p>
                        <p class="mb-1"><i class="bi bi-telephone me-2"></i> {{ $employee?->phone }}</p>
                        <p class="mb-2"><i class="bi bi-person-badge me-2"></i> Employee ID:
                            {{ $employee?->employee_id }}</p>

                        @if (auth()->id() == $employee?->user_id || auth()->user()->can('manage-employees'))
                            <div class="d-flex justify-content-end gap-1">
                                <button class="btn btn-sm btn-light" title="Edit Profile">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-light" title="Message">
                                    <i class="bi bi-chat-dots"></i>
                                </button>
                                <button class="btn btn-sm btn-light" title="Download PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ✅ Full HR Tab Navigation (Industry-Complete) -->
            <ul class="nav nav-tabs responsive-tab-nav mt-2" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button"
                        role="tab" aria-selected="true">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab"
                        aria-selected="false">Job & Compensation</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payroll" type="button"
                        role="tab" aria-selected="false">Payroll</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#benefits" type="button"
                        role="tab" aria-selected="false">Benefits</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#timeoff" type="button"
                        role="tab" aria-selected="false">Time Off</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents" type="button"
                        role="tab" aria-selected="false">Documents</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tasks" type="button"
                        role="tab" aria-selected="false">Tasks</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#performance" type="button"
                        role="tab" aria-selected="false">Performance</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity" type="button"
                        role="tab" aria-selected="false">Activity Logs</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="profileTabContent">
                <!-- Overview -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="card p-3 mb-3">
                        <h5>Personal Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Email:</strong> jane.doe@example.com</p>
                                <p><strong>Phone:</strong> +1 234 567 890</p>
                                <p><strong>Emergency Contact:</strong> John Doe (+1 987 654 321)</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Address:</strong> 123 Main St, San Francisco, CA</p>
                                <p><strong>Date of Birth:</strong> 12 Jan 1990</p>
                                <p><strong>Work Authorization:</strong> US Citizen</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Job & Compensation -->
                <div class="tab-pane fade" id="job" role="tabpanel">
                    <div class="card p-3 mb-3">
                        <h5>Job Details</h5>
                        <p><strong>Title:</strong> Software Engineer</p>
                        <p><strong>Department:</strong> Product</p>
                        <p><strong>Manager:</strong> John Smith</p>
                        <p><strong>Location:</strong> San Francisco, CA (Hybrid)</p>
                        <p><strong>Date Hired:</strong> 1 Mar 2022</p>
                        <p><strong>Employment Type:</strong> Full-time</p>
                    </div>
                    <div class="card p-3">
                        <h5>Compensation</h5>
                        <p><strong>Base Salary:</strong> $80,000 / year</p>
                        <p><strong>Annual Bonus Target:</strong> 10%</p>
                        <p><strong>Equity:</strong> 500 RSUs</p>
                    </div>
                </div>

                <!-- Payroll -->
                <div class="tab-pane fade" id="payroll" role="tabpanel">
                    <div class="card p-3 mb-3">
                        <h5>Pay Schedule</h5>
                        <p><strong>Frequency:</strong> Bi-weekly</p>
                        <p><strong>Next Pay Date:</strong> 5 Oct 2025</p>
                    </div>
                    <div class="card p-3 mb-3">
                        <h5>Bank Account</h5>
                        <p><strong>Account:</strong> ****4567 (Checking)</p>
                        <p><strong>Routing:</strong> ****1234</p>
                        <button class="btn btn-sm btn-outline-primary mt-2">Update Bank Info</button>
                    </div>
                    <div class="card p-3">
                        <h5>Tax Forms</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">W-4 (2025) – <span class="text-success">Completed</span></li>
                            <li class="list-group-item">State Tax Form – <span class="text-warning">Pending</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="tab-pane fade" id="benefits" role="tabpanel">
                    <div class="card p-3 mb-3">
                        <h5>Medical</h5>
                        <p><strong>Plan:</strong> PPO Gold</p>
                        <p><strong>Coverage:</strong> Employee + Spouse</p>
                        <p><strong>Monthly Premium:</strong> $120</p>
                    </div>
                    <div class="card p-3 mb-3">
                        <h5>Dental & Vision</h5>
                        <p>Dental: Basic Plan • Vision: Included</p>
                    </div>
                    <div class="card p-3">
                        <h5>401(k)</h5>
                        <p><strong>Contribution:</strong> 6% of salary</p>
                        <p><strong>Employer Match:</strong> 100% up to 4%</p>
                        <button class="btn btn-sm btn-outline-primary mt-2">View 401(k) Dashboard</button>
                    </div>
                </div>

                <!-- Time Off -->
                <div class="tab-pane fade" id="timeoff" role="tabpanel">
                    <div class="card p-3 mb-3">
                        <h5>PTO Balance</h5>
                        <p><strong>Vacation:</strong> 12 days available</p>
                        <p><strong>Sick Leave:</strong> 8 days available</p>
                        <p><strong>Carryover Limit:</strong> 5 days</p>
                    </div>
                    <div class="card p-3">
                        <h5>Recent Requests</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Oct 10–14, 2025 – Approved</li>
                            <li class="list-group-item">Aug 1, 2025 – Sick Leave – Approved</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-2">Request Time Off</button>
                    </div>
                </div>

                <!-- Documents -->
                <div class="tab-pane fade" id="documents" role="tabpanel">
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
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>2025-01-15</td>
                                </tr>
                                <tr>
                                    <td>Direct Deposit Form</td>
                                    <td>PDF</td>
                                    <td><span class="badge bg-success">Approved</span></td>
                                    <td>2025-02-01</td>
                                </tr>
                            </tbody>
                        </table>
                        <button class="btn btn-sm btn-outline-primary">Upload Document</button>
                    </div>
                </div>





                <!-- Tasks -->
                <div class="tab-pane fade" id="tasks" role="tabpanel">
                    <div class="card p-3">
                        <h5>Onboarding Checklist</h5>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Sign Employee Handbook
                                <span class="badge bg-success">Completed</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Complete I-9 Verification
                                <span class="badge bg-warning text-dark">Pending</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Set Up 2FA
                                <span class="badge bg-warning text-dark">Pending</span>
                            </li>
                        </ul>
                        <h5>Compliance Tasks</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Annual Security Training
                                <span class="badge bg-danger">Due in 3 days</span>
                            </li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-3">View All Tasks</button>
                    </div>
                </div>

                <!-- Performance -->
                <div class="tab-pane fade" id="performance" role="tabpanel">
                    <div class="card p-3 mb-3">
                        <h5>Current Goals (2025)</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Improve System Reliability</strong><br>
                                <small>Reduce production incidents by 30%</small><br>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: 60%"></div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <strong>Mentor Junior Engineers</strong><br>
                                <small>Onboard 2 new team members</small><br>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: 40%"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="card p-3">
                        <h5>Recent Reviews</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Q2 2025 Review</strong> – <span class="text-success">Exceeds
                                    Expectations</span><br>
                                <small>Completed on 30 Jun 2025</small>
                            </li>
                            <li class="list-group-item">
                                <strong>Year-End 2024</strong> – <span class="text-success">Meets
                                    Expectations</span><br>
                                <small>Completed on 15 Dec 2024</small>
                            </li>
                        </ul>
                        <button class="btn btn-sm btn-outline-primary mt-2">View Full Review History</button>
                    </div>
                </div>




                <!-- Activity Logs -->
                <div class="tab-pane fade" id="activity" role="tabpanel">
                    <div class="card p-3">
                        <h5>Recent Activity</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Logged in at 9:00 AM, 22 Sep 2025</li>
                            <li class="list-group-item">Updated bank details on 20 Sep 2025</li>
                            <li class="list-group-item">Submitted PTO request on 18 Sep 2025</li>
                            <li class="list-group-item">Viewed pay stub on 15 Sep 2025</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>



    @endif
</div>
