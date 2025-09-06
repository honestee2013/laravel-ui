<div
    x-data="bottomBarComponent({{ $maxVisible }})"
    class="d-md-none position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-sm"
    style="z-index:1030;" {{-- keep above content --}}
>
<div class="d-flex px-2 py-1" style="gap:.5rem; overflow-x:auto; overflow-y:visible;">

    {{-- Visible buttons --}}
    <template x-for="(item, idx) in visibleItems" :key="item.key">
      <a :href="item.route" class="btn btn-light flex-shrink-0 text-center" style="min-width:70px;">
        <i :class="`fa ${item.icon} d-block mb-1`"></i>
        <small x-text="item.label"></small>
      </a>
    </template>

<!-- More Button -->
<div class="btn-group dropup flex-shrink-0 d-md-block d-none">
  <!-- Desktop Dropdown -->
  <button class="btn btn-light dropdown-toggle"
          data-bs-toggle="dropdown"
          data-bs-display="static"
          data-bs-boundary="viewport">
    <i class="fa fa-ellipsis-h"></i>
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <template x-for="item in overflowItems" :key="item.key">
      <li>
        <a :href="item.route" class="dropdown-item d-flex align-items-center">
          <i :class="`fa ${item.icon} me-2`"></i>
          <span x-text="item.label"></span>
        </a>
      </li>
    </template>
  </ul>
</div>

<!-- Mobile Bottom Sheet -->
<div class="d-md-none">
  <button class="btn btn-light" data-bs-toggle="offcanvas" data-bs-target="#mobileMoreSheet">
    <i class="fa fa-ellipsis-h"></i>
  </button>

  <div class="offcanvas offcanvas-bottom" tabindex="-1" id="mobileMoreSheet">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">More</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
      <ul class="list-group list-group-flush">
        <template x-for="item in overflowItems" :key="item.key">
          <li class="list-group-item">
            <a :href="item.route" class="d-flex align-items-center">
              <i :class="`fa ${item.icon} me-2`"></i>
              <span x-text="item.label"></span>
            </a>
          </li>
        </template>
      </ul>
    </div>
  </div>
</div>


  </div>
</div>

<script>
  function bottomBarComponent(maxVisible) {
    return {
      items: @json($items),
      maxVisible: maxVisible,
      get visibleItems() {
        return this.items.slice(0, this.maxVisible);
      },
      get overflowItems() {
        return this.items.slice(this.maxVisible);
      }
    }
  }
</script>
