<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h3>Data Export</h3>
    <table>
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th>{{ $fieldDefs[$col]['label'] ?? ucfirst(str_replace('_', ' ', $col)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    @foreach($columns as $col)
                        @php
                            $value = '';
                            $def = $fieldDefs[$col] ?? [];

                            if (isset($def['relationship'])) {
                                $rel = $def['relationship'];
                                $dp = $rel['dynamic_property'];
                                $df = $rel['display_field'] ?? 'name';

                                if (in_array($rel['type'], ['hasMany', 'belongsToMany'])) {
                                    $value = $row->$dp?->pluck($df)->implode(', ');
                                } else {
                                    $value = $row->$dp?->{$df} ?? '';
                                }
                            } else {
                                $value = $row->$col;
                            }
                        @endphp
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
