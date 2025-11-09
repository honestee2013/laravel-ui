<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Menus;


use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Modules\Access\Http\Livewire\AccessControls\AccessControlManager;
use Illuminate\Support\Facades\Auth;


class MenuInitializer extends Component
{

    public $moduleName;

    protected $listeners = [
        "initializeMenuEvent" => "initializeMenu"
    ];





    public function initializeMenu() {
        $user = Auth::user();
        $sidebarLinks = $this->getSidebarLinks($user);
        $sidebarLinks = $this->getSidebarLinks($user);
        $this->dispatch('sidebar-data-loaded', $sidebarLinks);
    }





    private function getSidebarLinks($user)
    {
        $header = $this->getSidebarHeader();
        $body = $this->loadSidebarLinks($this->moduleName);
        $footer = $this->getSidebarFooter();
        $links = array_merge($header, $body, $footer);


        return collect($links)
            ->filter(fn ($link) => !isset($link['permission']) || $user->can($link['permission']))
            ->map(function ($link) {
                // If no URL is provided for a top-level item with a submenu,
                // we don't want to create a clickable link for it.
                if (isset($link['submenu']) && !isset($link['url'])) {
                    return $link;
                }
                // Ensure there's a default '#' if no URL is provided for non-submenu items
                $link['url'] = $link['url'] ?? '#';
                return $link;
            })
            ->values()
            ->toArray();
    }


    private function getSidebarHeader() {
        return [
            [
                'title' => 'System Modules',
                'icon' => 'fas fa-th fs-4 ms-2',
                'url' => 'system/modules',
                'cssClasses' => "menu-item-header menu-header",
                'itemType' => 'header',
                
                //'permission' => 'view-dashboard'
            ],
            [
                'itemType' => 'item-separator',
                'title' => '<hr class="horizontal dark my-4 item-separator" />',
                //'permission' => 'view-dashboard'
            ],
            [
                'title' => 'Module\'s Dashboard',
                'icon' => 'fas fa-tachometer-alt fs-5 ms-2',
                'url' => strtolower($this->moduleName) . '/dashboard',
                'cssClasses' => "menu-item-header",
                'itemType' => 'header',
                //'permission' => 'view-dashboard'
            ],
            /*[
                'itemType' => 'item-separator',
                'title' => '<h6 class="ps-3 my-3 text-uppercase text-xs font-weight-bolder opacity-6 group-title">Manage Payment Structure</h6>',
                //'permission' => 'view-dashboard'
            ],*/



        ];
    }


    private function getSidebarFooter() {




        return [
            [
                'itemType' => 'item-separator',
                'title' => '<hr class="horizontal dark my-4 item-separator" />',
                //'permission' => 'view-dashboard'
            ],
            [
               'title' => 'Access Control',
                'icon' => 'fas fa-key sidebar-icon',
                'url' => "access/access-control-management/$this->moduleName",
                'cssClasses' => "menu-item-footer menu-footer",
                'itemType' => 'footer',
                //'permission' => 'view-dashboard'
            ],




            /*[
               'title' => 'Settings',
                'icon' => 'fas fa-cogs sidebar-icon',
                'url' => strtolower($this->moduleName) . '/settings',
                'cssClasses' => "menu-item-footer menu-footer",
                'itemType' => 'footer',
                //'permission' => 'view-dashboard'
            ],
            [
                'title' => 'Advance',
                'icon' => 'fas fa-gear sidebar-icon',
                'url' => strtolower($this->moduleName) . '/advance',
                'cssClasses' => "menu-item-footer menu-footer",
                'itemType' => 'footer',
                //'permission' => 'view-advance'
            ],*/
        ];
    }




    function loadSidebarLinks(string $moduleName): array {
        $path = base_path("app/Modules/" . ucfirst($moduleName) . "/Config/sidebar_menu.php");
        return file_exists($path) ? include $path : [];
    }


    public function render()
    {
        return view('system.views::menus.menu-initializer');
    }
    



}