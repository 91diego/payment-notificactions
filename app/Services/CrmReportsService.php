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
     * Generate leads report
     * @param phase $phase
     * PROSPECTO ASIGNADO (STATUS_ID) -> IN_PROCESS
     * PROSPECTO EN SEGUIMIENTO (STATUS_ID) -> 3
     * DUPLICADOS (STATUS_ID) -> 5
     * PENDIENTE (STATUS_ID) -> 4
     * NO CALIFICA (STATUS_ID) -> JUNK
     * CALIFICADO (STATUS_ID) -> CONVERTED
     */
    public function createLeadsReport($phase)
    {
        return $this->crmReportsRepository->createLeadsReport($phase);
    }

    /**
     * Generate comercial report
     */
    public function comercialReport()
    {
        return $this->crmReportsRepository->comercialReport();
    }
}
