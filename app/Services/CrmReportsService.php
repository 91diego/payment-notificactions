<?php

namespace App\Services;

use App\Repositories\CrmReportsRepository;

class CrmReportsService
{
    protected $crmReportsRepository;

    public function __construct(CrmReportsRepository $crmReportsRepository)
    {
        $this->crmReportsRepository = $crmReportsRepository;
    }

    /**
     * Generate comercial report
     */
    public function comercialReport()
    {
        return $this->crmReportsRepository->comercialReport();
    }
}
