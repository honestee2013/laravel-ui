<div class="sidenav-header">
    <a class="align-items-center d-flex m-0 navbar-brand text-wrap text-primary text-gradient" href="/core/modules">
       {{--<img src="../assets/img/logo-ct.png" class="navbar-brand-img h-100" alt="...">--}}
        <i id="sidebarLogo" class="fas fa-th-large fs-5"></i>
        <span id = "sidebarLogoText" class="ms-3 font-weight-bold"> System Modules</span>
    </a>


</div>

<hr class="horizontal dark mt-0" />


<x-core.views::layouts.navbars.sidebar-link-item
    iconClasses="fas fa-tachometer-alt sidebar-icon"
    url="{{strtolower($moduleName)}}/dashboard"
    title="Dashboard"
/>


