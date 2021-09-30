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
     * Generate comercial report
     */
    public function comercialReport()
    {
        $comercialReport = new ComercialReportExport([$this->crmReportsService->comercialReport()]);
        return Excel::download($comercialReport, 'reporte_comercial.xlsx');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
