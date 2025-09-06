<?php

namespace QuickerFaster\LaravelUI\Components\Livewire\Layouts;


use Livewire\Component;
use QuickerFaster\LaravelUI\Traits\HasNavItems; // <-- Import the trait


class TopNav extends Component
{
    use HasNavItems; // <-- Use the trait

    // Public props can be passed when mounting the component
    public array $items = []; // each item: ['key'=>'home','label'=>'Home','route'=>'home','icon'=>'fa-home','visible'=>true]
    public string $activeKey = ''; // current active item key
    public int $maxDesktop = 5;
    public int $maxMobile = 3;

    protected $listeners = [
        'localeChanged' => 'onLocaleChanged',
        'setActiveNav' => 'setActive',
    ];

    public function mount(array $items = [], string $activeKey = '')
    {
        $this->items = $items ?: $this->defaultNavItems();
        $this->activeKey = $activeKey;
    }


    // Called from JS/Alpine via Livewire.emit if needed (keeps logic available)
    public function setActive(string $key)
    {
        $this->activeKey = $key;
    }

    /*public function onLocaleChanged(string $locale)
    {
        dd("Locale changed to: $locale");
        // Example server-side handler if you need to persist locale
        app()->setLocale($locale);
        // optionally persist to user profile, session etc.
    }*/


    // Livewire v3+
    //#[On('localeChanged')]
    /*public function onLocaleChanged(string $locale)
    {
        // Set the locale in the session (optional, but good practice for persistence)
        session()->put('locale', $locale);
        app()->setLocale($locale);
        // Set the locale for the current request

        // This will refresh the entire page to apply the new locale
        //return redirect(request()->header('Referer'));
        //return $this->redirect(request()->header('Referer'), navigate: true);


        // Or, for a full-page refresh, you can just dispatch a browser event.
        // $this->dispatch('localeChanged');
    }*/


    public function onLocaleChanged(string $locale)
    {
        // Set the locale in the session
        session()->put('locale', $locale);

        // Set the locale for the current request
        //app()->setLocale($locale);
        \App::setLocale($locale);

        // Refresh the component and its children
        //$this->dispatch('$refresh');
    }





    public function logout()
    {
        auth()->logout();
        redirect()->route('login');
    }

    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        return view("qf::layouts.livewire.$UIFramework.top-nav")
            ->layout("qf::layouts.livewire.$UIFramework.app"); // 👈 important
    }
}
