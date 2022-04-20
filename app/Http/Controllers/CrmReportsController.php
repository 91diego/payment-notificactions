<?php

namespace App\Http\Controllers;

use App\Exports\ComercialReportExport;
use App\Services\CrmReportsService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CrmReportsController extends Controller
{

    protected $crmReportsService;

    /**
     * Controller constructor
     */
    public function __construct(CrmReportsService $crmReportsService)
    {
        $this->crmReportsService = $crmReportsService;
    }

    /**
     * Generate deal report
     * @param type $type
     * DEAL-SELL, CATEGORY_ID 0
     * DEAL-NEGOTATION, CATEGORY_ID 1
     */
    public function createDealReport($category)
    {
        return $this->crmReportsService->createDealReport($category);
    }

    /**
     * Generate leads report
     * @param phase $phase
     * PROSPECTO ASIGNADO (STATUS_ID) -> IN_PROCESS
     * PROSPECTO EN SEGUIMIENTO (STATUS_ID) -> 3
     * DUPLICADOS (STATUS_ID) -> 5
     * PENDIENTE (STATUS_ID) -> 4
     * NO CALIFICA (STATUS_ID) -> JUNK
     * CALIFICADO (STATUS_ID) -> CONVERTED
     */
    public function createLeadsReport(Request $request)
    {
        return $this->crmReportsService->createLeadsReport($request->all());
    }

    /**
     * Update leads report
     */
    public function updateLeadsReport()
    {
        return $this->crmReportsService->updateLeadsReport();
    }

    /**
     * Update deals report
     */
    public function updateDealsReport($category)
    {
        return $this->crmReportsService->updateDealsReport($category);
    }

    /**
     * Generate comercial report
     */
    public function comercialReport()
    {
        $comercialReport = new ComercialReportExport([$this->crmReportsService->comercialReport()]);
        return Excel::download($comercialReport, 'reporte_comercial.xlsx');
    }
}
