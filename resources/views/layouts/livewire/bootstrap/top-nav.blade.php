<div
    x-data="topNavComponent({{ json_encode($items)}}, {{ $maxDesktop }}, {{ $maxMobile }})"
    class="navbar navbar-expand-md navbar-light bg-white shadow-sm"
    x-init="init()"
>
  <div class="container-fluid">
    {{-- Brand --}}
    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
      <i class="fas fa-bolt me-2"></i>
      <span class="fw-bold">QuickerFaster</span>
    </a>

    {{-- Toggler for mobile --}}
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavCollapse">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topNavCollapse">
      {{-- Left: nav items (desktop) --}}
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-none d-md-flex">
        <template x-for="(item, idx) in visibleDesktop" :key="item.key">
          <li class="nav-item">
            <a :href="item.route"
               class="nav-link"
               :class="{'active fw-semibold': item.key === activeKey}"
               x-on:click.prevent="onClick(item)">
              <i :class="`fa ${item.icon} me-1`" aria-hidden="true"></i>
              <span x-text="item.label"></span>
            </a>
          </li>
        </template>

        {{-- overflow dropdown --}}
        <li class="nav-item dropdown" x-show="overflowDesktop.length > 0" x-cloak>
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            {{ __('qf::nav.more') }}
          </a>
          <ul class="dropdown-menu">
            <template x-for="item in overflowDesktop" :key="item.key">
              <li>
                <a :href="item.route" class="dropdown-item" x-text="item.label" x-on:click.prevent="onClick(item)"></a>
              </li>
            </template>
          </ul>
        </li>
      </ul>

      {{-- Center or search area (optional) --}}
      <div class="mx-auto d-none d-md-block">
        {{-- Small search (example) --}}
        <div class="input-group" x-data="{ q:'' }" x-on:keyup.debounce.300ms="$dispatch('top-nav:search', q)">
          <input x-model="searchQuery" x-on:input.debounce.300ms="emitSearch"
                 class="form-control form-control-sm" type="search" :placeholder="$store.i18n.searchPlaceholder">
          <button class="btn btn-sm btn-outline-secondary" type="button" x-on:click="clearSearch()">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      {{-- Right: actions / mobile nav (shows up to 3 + swipeable) --}}
      <div class="d-flex align-items-center">
        <div class="d-md-none mobile-scroll-wrapper me-2" x-cloak>
          <div class="d-flex overflow-auto" style="gap:.5rem;">
            <template x-for="(item, idx) in visibleMobile" :key="item.key">
              <a :href="item.route" class="btn btn-light btn-sm" x-on:click.prevent="onClick(item)">
                <i :class="`fa ${item.icon} me-1`"></i><span x-text="item.label"></span>
              </a>
            </template>

            <template x-if="overflowMobile.length > 0">
                <div class="btn-group position-static">
                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown"></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                    <template x-for="item in overflowMobile" :key="item.key">
                        <li><a :href="item.route" class="dropdown-item" x-text="item.label" x-on:click.prevent="onClick(item)"></a></li>
                    </template>
                    </ul>
                </div>
            </template>


          </div>
        </div>

        {{-- Locale switcher example --}}
        <div class="me-2">
          <select class="form-select form-select-sm" x-on:change="changeLocale($event.target.value)" aria-label="{{ __('qf::nav.locale') }}">
            <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>EN</option>
            <option value="fr" {{ app()->getLocale() === 'fr' ? 'selected' : '' }}>FR</option>
            <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>ES</option>
            {{-- add more locales --}}
          </select>
        </div>

        {{-- Profile / logout --}}
        <div class="dropdown">
          <a class="btn btn-sm btn-outline-primary dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-user me-1"></i> <span class="d-none d-md-inline">{{ auth()->user()?->name ?: __('qf::nav.account') }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('profile') }}">{{ __('qf::nav.profile') }}</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <button class="dropdown-item text-danger" type="button" x-on:click.prevent="$wire.logout()">{{ __('qf::nav.logout') }}</button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  {{-- Alpine component script (inlined for simplicity) --}}
  <script>
    function topNavComponent(items, maxDesktop = 5, maxMobile = 3) {
      return {
        items: items || [],
        activeKey: @json($activeKey),
        maxDesktop: maxDesktop,
        maxMobile: maxMobile,
        searchQuery: '',
        init() {

          // ensure i18n store exists
          if (!Alpine.store('i18n')) {
            Alpine.store('i18n', { searchPlaceholder: "{{ __('qf::nav.search_placeholder') }}" });
          }
        },
        get visibleDesktop() {
          return this.items.slice(0, this.maxDesktop);
        },
        get overflowDesktop() {
          return this.items.slice(this.maxDesktop);
        },
        get visibleMobile() {
          return this.items.slice(0, this.maxMobile);
        },
        get overflowMobile() {
          return this.items.slice(this.maxMobile);
        },
        onClick(item) {
          this.activeKey = item.key;
          // Use client-side nav for SPA-like feel; fallback to full load via location
          if (item.route && item.route !== '#') {
            window.location.href = item.route;
          } else {
            // If route is Livewire action, emit
            if (item.livewireAction) {
              Livewire.emit(item.livewireAction, item);
            }
          }
        },
        changeLocale(locale) {
          // Minimal client: emit to Livewire to persist if needed
          Livewire.dispatch('localeChanged', [locale]);
          // quick client reload to apply new translations
          window.location.reload();
        },
        emitSearch() {
          // Use Alpine's debounce by harnessing a timer
          if (this._searchTimer) clearTimeout(this._searchTimer);
          this._searchTimer = setTimeout(() => {
            this.$dispatch('top-nav:search', this.searchQuery);
          }, 300);
        },
        clearSearch() {
          this.searchQuery = '';
          this.$dispatch('top-nav:search', '');
        }
      }
    }

document.addEventListener('top-nav:search', e => {
  // e.detail is the search string
  // You can call Livewire.emit('tableSearch', e.detail) or fetch() an endpoint
  alert(e.detail)
  // Livewire.emit('globalSearch', e.detail);
});

  </script>

  <style>
    /* small helper to make mobile buttons scroll nicely */
    .mobile-scroll-wrapper .btn { white-space: nowrap; }
  </style>
</div>
