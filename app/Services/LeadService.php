<?php

namespace App\Services;

use App\Repositories\LeadRepository;

class LeadService
{
    protected $leadRepository;

    public function __construct(LeadRepository $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    public function index($request)
    {

    }

    /**
     * GetLead gets lead data from B24
     */
    public function getLead($request)
    {
        return $this->leadRepository->getLead($request);
    }
}
