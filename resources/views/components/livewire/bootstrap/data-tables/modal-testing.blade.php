

<div class="modal fade" id="{{$modalId}}" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1" wire:key="{{$modalId}}">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalToggleLabel">Modal {{$modalId}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>


        <div class="modal-body">
            @include('core.views::data-tables.partials.data-table-form')
        </div>


        <div class="modal-footer">
            <button type="button" class="btn bg-gradient-secondary rounded-pill"
                data-bs-dismiss="modal">Close</button>
            <button type="button"
                onclick="Livewire.dispatch('addRelationshipRecordEvent', {{$fields}})"
                class="btn bg-gradient-primary rounded-pill"
               > Add Record </button>
        </div>
    </div>
</div>
