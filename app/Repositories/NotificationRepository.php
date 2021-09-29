<?php

namespace App\Repositories;

use App\Models\Connection;
use App\Services\ConnectionService;
use Illuminate\Support\Facades\DB;

class NotificationRepository
{
    // use Response, ValidateRequest;

    /**
     * Constructor
     */
    Public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $code = 200;
        $status = 'success';
        $message = '¡Los articulos han sido listados con éxito!';
        $items = null;
        DB::beginTransaction();
        try {
            $connections = $this->connectionService->index();
            dd($connections);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $code = 500;
            $status = 'error';
            $message = 'Ha ocurrido un error';
        }
        // return $this->apiResponse($code, $status, $message, $items);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $request
     */
    public function store($request)
    {
        // TODO
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        // TODO
    }
}
