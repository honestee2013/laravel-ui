

<div class="table-responsive p-0">
    {{---------------- SPINNER ------------------}}
    @include('qf::components.livewire.bootstrap.widgets.spinner')
    {{---------------- TABLE ------------------}}
    {{--@include('qf::components.livewire.bootstrap.data-tables.partials.table-header')--}}
    @include('qf::components.livewire.bootstrap.data-tables.partials.table-body')
    @include('qf::components.livewire.bootstrap.data-tables.partials.table-footer', ["data" => $data])
</div>



