<x-core.views::layouts.navbars.sidebar-link-item
    iconClasses="fas fa-toggle-on"
    url="user/user-statuses"
    title="User Statuses"
    anchorClasses="{{ ($active == 'user-statuses')? 'active': ''}}"
/>


<x-core.views::layouts.navbars.sidebar-link-item
    iconClasses="fas fa-folder"
    url="user/user-status-categories"
    title="User Status Categories"
    anchorClasses="{{ ($active == 'user-status-categories')? 'active': ''}}"
/>

