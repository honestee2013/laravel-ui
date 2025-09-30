<hr class = 'horizontal dark' /> 

@if(auth()->user()?->can('view_user'))
    <x-core.views::layouts.navbars.sidebar-link-item
        iconClasses="fas fa-users sidebar-icon"
        url="user/users"
        title="Manage Users"
    />
@endif


@if(auth()->user()?->can('view_job_title'))
    <x-core.views::layouts.navbars.sidebar-link-item
        iconClasses="fas fa-briefcase sidebar-icon"
        url="user/job-titles"
        title="Job Titles"
    />
@endif


@if(auth()->user()?->can('view_employee_profile'))
    <x-core.views::layouts.navbars.sidebar-link-item
        iconClasses="fas fa-id-card-alt sidebar-icon"
        url="user/employee-profiles"
        title="Employee Profiles"
    />
@endif


@if(auth()->user()?->can('view_user_status'))
    <x-core.views::layouts.navbars.sidebar-link-item
        iconClasses="fas fas fa-user-tie sidebar-icon"
        url="user/user-statuses"
        title="Manage User Statuses"
    />
@endif


@if(auth()->user()?->can('view_user_status_category'))
    <x-core.views::layouts.navbars.sidebar-link-item
        iconClasses="fas fas fa-user-friends sidebar-icon"
        url="user/user-status-categories"
        title="User Status  Categories"
    />
@endif
