<div class="mt-3 ms-3 me-4 d-flex justify-content-between align-items-center">
    <div>
        <p class="text-sm text-secondary">
            Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of {{ $data->total() }}
            results
        </p>
    </div>
    <div>
        {!! $data->links('pagination') !!}

    </div>
</div>


