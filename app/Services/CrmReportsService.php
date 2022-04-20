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
     * Generate deal report
     * @param category $category
     * DEAL-SELL, CATEGORY_ID 0
     * DEAL-NEGOTATION, CATEGORY_ID 1
     */
    public function createDealReport($category)
    {
        return $this->crmReportsRepository->createDealReport($category);
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
    public function createLeadsReport($request)
    {
        return $this->crmReportsRepository->createLeadsReport($request);
    }

    /**
     * Update leads report
     */
    public function updateLeadsReport()
    {
        return $this->crmReportsRepository->updateLeadsReport();
    }

    /**
     * Update deals report
     */
    public function updateDealsReport($category)
    {
        return $this->crmReportsRepository->updateDealsReport($category);
    }

    /**
     * Generate comercial report
     */
    public function comercialReport()
    {
        return $this->crmReportsRepository->comercialReport();
    }
}
