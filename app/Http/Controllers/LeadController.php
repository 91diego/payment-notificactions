<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Traits\LogTrait;
use Illuminate\Http\Request;
use App\Services\LeadService;

class LeadController extends Controller
{
    use LogTrait;

    protected $leadService;

    /**
     * Controller constructor
     */
    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * GetLead gets lead data from B24
     */
    public function getLead(Request $request)
    {
        $this->writeToLog($_REQUEST, " INCOMING LEAD", "leads_entrantes");
        return $this->leadService->getLead($request);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDealById(Request $request)
    {
        $dealById = $this->leadService->getDealById($request->all());
        return $this->leadService->storeDealById($dealById);
    }
}
