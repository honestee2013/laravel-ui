<x-core.views::layouts.navbars.sidebar-link-item
    iconClasses="fas fa-user"
    url="user/users"
    title="User Overview"
    anchorClasses="{{ ($active == 'users')? 'active': ''}}"
/>


<x-core.views::layouts.navbars.sidebar-link-item
    iconClasses="fas fa-address-card"
    url="user/basic-infos"
    title="Basic Info"
    anchorClasses="{{ ($active == 'basic-infos')? 'active': ''}}"
/>

