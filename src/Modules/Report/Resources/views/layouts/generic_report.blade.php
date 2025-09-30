    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    @php
        $modelTitle = $reportTitle ?? 'Report';
        $headerFields = $config['headerFields'] ?? [];
        $tableFields = $config['tableFields'] ?? [];
        $summaryFields = $config['summaryFields'] ?? [];
        $signatories = $config['signatories'] ?? [];
        $fieldDefinitions = $config['fieldDefinitions'] ?? [];

        $isPdf = request()->routeIs('reports.export');

    @endphp





    <div class="{{ $isPdf ? '' : 'container my-4 p-4 bg-white shadow-sm border  rounded-3' }}"
        style="{{ $isPdf ? 'font-family: DejaVu Sans; font-size:12px;' : '' }}">


        @if (!$isPdf)
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('reports.export', ['modelName' => 'PayrollRun', 'id' => $reportHeader->id, 'format' => 'pdf']) }}"
                    class="btn btn-sm btn-outline-danger me-2" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                </a>
                <a href="{{ route('reports.export', ['modelName' => 'PayrollRun', 'id' => $reportHeader->id, 'format' => 'excel']) }}"
                    class="btn btn-sm btn-outline-success" target="_blank">
                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                </a>
            </div>
        @endif







        <h2 class="{{ $isPdf ? '' : 'text-center mb-4' }}">{{ $modelTitle }}</h2>

        {{-- HEADER --}}
        <table class="{{ $isPdf ? '' : 'table table-bordered' }}" width="100%"
            style="{{ $isPdf ? 'margin-bottom:20px; margin-bottom:20px;' : '' }}">
            @foreach ($headerFields as $field)
                <tr>
                    <td style="{{ $isPdf ? 'width:30%; font-weight:bold;' : '' }}">
                        {{ $fieldDefinitions[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field)) }}
                    </td>
                    <td>{{ $reportHeader->$field ?? '' }}</td>
                </tr>
            @endforeach
        </table>
        <br /><br />

        {{-- RECORDS --}}

        {{-- TABLE --}}
        <table class="{{ $isPdf ? '' : 'table table-striped table-bordered' }}" border="{{ $isPdf ? 1 : 0 }}"
            width="100%" style="{{ $isPdf ? 'border-collapse:collapse; margin-bottom:20px;' : '' }}">
            <thead>
                <tr style="{{ $isPdf ? 'background-color: #f2f2f2;' : '' }}">
                    @foreach ($tableFields as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($reportItems as $row)
                    <tr>
                        @foreach ($tableFields as $key => $label)
                            <td>{{ $row->$key }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <br /><br />

        {{-- SUMMARY --}}
        @if (count($summaryFields))
            <h4>Summary</h4>
            <table class="{{ $isPdf ? '' : 'table table-bordered' }}" width="100%">
                @foreach ($summaryFields as $key => $label)
                    <tr>
                        <td style="{{ $isPdf ? 'width:30%; font-weight:bold;' : '' }}">{{ $label }}</td>
                        <td>{{ number_format($reportItems->sum($key), 2) }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        <br /><br />
        {{-- SIGNATORIES --}}
        @if (count($signatories))
            <h4>Signatories</h4>
            <table width="100%" cellpadding="{{ $isPdf ? '20' : '10' }}">
                <tr>
                    @foreach ($signatories as $signatory)
                        <td class="text-center">
                            ___________________________<br>
                            <strong>{{ $signatory['label'] }}</strong><br>
                            {{ $reportHeader->{$signatory['field']} ?? '' }}
                        </td>
                    @endforeach
                </tr>
            </table>
        @endif

    </div>



