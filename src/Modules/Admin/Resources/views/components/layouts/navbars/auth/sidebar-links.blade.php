

<li class="nav-inventory mt-4">
    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">User Roles & Admin Controls</h6>
</li>

@hasanyrole('admin|super_admin')
<system.views::layouts.navbars.sidebar-link-item
        iconClasses="fas fa-users-cog sidebar-icon"
        url="access/user-role-management"
        title="Manage Roles"
    />
@endhasanyrole

@hasanyrole('admin|super_admin')
<system.views::layouts.navbars.sidebar-link-item
        iconClasses="fas fa-user-shield sidebar-icon"
        url="access/user-role-assignment"
        title="Assign User Roles"
    />
@endhasanyrole

@hasanyrole('admin|super_admin')
<system.views::layouts.navbars.sidebar-link-item
        iconClasses="fas fa-user-lock sidebar-icon"
        url="access/access-control-management"
        title="Manage Permission"
    />
@endhasanyrole



{{--<system.views::layouts.navbars.sidebar-link-item
    iconClasses="fas fa-exchange-alt sidebar-icon"
    url="access/teams"
    title="Manage Teams"
/>


<system.views::layouts.navbars.sidebar-link-item
    iconClasses="fas fa-sync-alt sidebar-icon"
    url="access/permissions"
    title="Manage Permissions"
/>--}}





