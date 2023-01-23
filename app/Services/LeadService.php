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

    /**
     * Store a newly created resource in storage.
     * @param $request
     */
    public function getDealById($request)
    {
        return $this->leadRepository->getDealById($request);
    }

    /**
     * Store deal by id when is created on BITRIX24
     * @param $request
     */
    public function storeDealById($request)
    {
        return $this->leadRepository->storeDealById($request);
    }
}
