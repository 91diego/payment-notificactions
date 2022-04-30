<?php

namespace App\Repositories;

use Exception;
use App\Models\Connection;

class ConnectionRepository
{

    public function index($request)
    {
        try {
            $result = Connection::where('name', $request)->get();
        } catch (Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }
}
