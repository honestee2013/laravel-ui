@if ($isCompleted)
    @php
        $completion = $this->getCompletionConfig();
    @endphp

    <div class="card text-center">
        <div class="card-body">
            <div class="mb-4">
                <div class="icon-circle bg-success text-white mx-auto mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-check fa-2x"></i>
                </div>
                <h2 class="card-title">{{ $completion['title'] }}</h2>
                @if (!empty($completion['message']))
                    <p class="text-muted">{{ $completion['message'] }}</p>
                @endif
            </div>

            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                @foreach($completion['actions'] as $index => $action)
                    @php
                        $isPrimary = ($index === 0) || ($action['primary'] ?? false);
                    @endphp
                    <a 
                        href="{{ $action['url'] }}" 
                        class="btn {{ $isPrimary ? 'btn-primary' : 'btn-outline-secondary' }} px-4"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif