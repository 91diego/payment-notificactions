<?php

namespace App\Repositories;

use Exception;
use App\Models\Lead;
use App\Traits\BitrixTrait;

class LeadRepository
{
    use BitrixTrait;

    public function getLead($request)
    {
        try {
            $lead = $this->getLeadByIdB24($request->id);
            $validateLeadOnDb = Lead::where('prospecto_bitrix_id', $request->id)->get();
            if (count($validateLeadOnDb) == 0) {
                Lead::create($lead);
                $message = "creado correctamente";
            }else {
                // update
                Lead::where('prospecto_bitrix_id', $lead['prospecto_bitrix_id'])
                    ->update($lead);
                $message = "actualizado correctamente";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return $message;
    }
}
