

<div class="table-responsive p-0">
    {{---------------- SPINNER ------------------}}
    @include('core.views::widgets.spinner')
    {{---------------- TABLE ------------------}}
    @include('core.views::data-tables.partials.table-header')
    @include('core.views::data-tables.partials.table-body')
    @include('core.views::data-tables.partials.table-footer')
</div>



