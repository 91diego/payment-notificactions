<?php

namespace App\Services;

use App\Repositories\DealRepository;

class DealService
{
    protected $dealRepository;

    public function __construct(DealRepository $dealRepository)
    {
        $this->dealRepository = $dealRepository;
    }

    /**
     * setStage call external api to set status
     * @param  \Illuminate\Http\Request  $request
     */
    public function getDealStage($request)
    {
        return $this->dealRepository->getDealStage($request);
    }
}
