<div>




<div
    x-data="sidebarComponent(@entangle('state'))"
    class="d-none d-md-flex" {{-- Hides on <768px (mobile) --}}
>
  {{-- Sidebar container --}}
  <div
      class="bg-light border-end vh-100 d-flex flex-column align-items-stretch"
      :class="{
          'sidebar-full': state === 'full',
          'sidebar-icon': state === 'icon',
          'sidebar-hidden': state === 'hidden'
      }"
      x-transition
  >
      {{-- Nav items --}}
      <ul class="nav flex-column mt-3">
        <template x-for="item in items" :key="item.key">
          <li class="nav-item text-nowrap">
            <a 
              :href="item.route" 
              class="nav-link d-flex align-items-center"
              data-bs-toggle="tooltip"
              data-bs-placement="right"
              :title="item.label"
              wire:ignore.self
            >
              <i :class="`fa ${item.icon} me-2`"></i>
              <span x-show="state === 'full'" x-transition x-text="item.label"></span>
            </a>
          </li>
        </template>
      </ul>


    




  </div>

  {{-- Toggle handle (always visible on desktop) --}}
  <div class="d-flex align-items-center justify-content-center toggle-handle bg-light border-end"
       x-on:click="toggle()"
       style="cursor:pointer; width:20px;"
  >
      <i class="fa"
         :class="{
            'fa-chevron-left': state === 'full' || state === 'icon',
            'fa-chevron-right': state === 'hidden'
         }">
      </i>
  </div>
</div>






<style>
  .sidebar-full { width: 220px; min-width: 220px; }
  .sidebar-icon { width: 60px; min-width: 60px; }
  .sidebar-hidden { width: 0; min-width: 0; overflow: hidden; }
  .sidebar-hidden .nav-link { display:none; }
</style>

<script>
  function sidebarComponent(stateRef) {
    return {
      state: stateRef, // entangled with Livewire
      items: @json($items),
      toggle() {
        if (this.state === 'full') {
          this.state = 'icon';
        } else if (this.state === 'icon') {
          this.state = 'hidden';
        } else {
          this.state = 'full';
        }
      }
    }
  }
</script>





</div>
