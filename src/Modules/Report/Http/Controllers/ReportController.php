<?php

namespace App\Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Report\Services\ReportService;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Modules\Report\Exports\ReportExport;







class ReportController extends Controller
{

    public function show($report, $id)
    {

        $service = ReportService::make($report, $id, request()->moduleName);
        return view('report.views::layouts.generic_report', [
            'isPdf' => request()->format === 'pdf',
             ...$service->getViewData()
        ]);



    }

    public function export($report, $id, $format)
    {
        $service = ReportService::make($report, $id, request()->moduleName);

        if ($format === 'pdf') {
            $pdf = \PDF::loadView('report.views::layouts.generic_report', $service->getViewData());
            return $pdf->download("{$report}_report.pdf");
        }

        if ($format === 'excel') {
            return Excel::download(new ReportExport($service->getExportData()), request()->moduleName."_report.xlsx");
        }

        abort(404);
    }


}
