<?php

namespace App\Repositories;

use App\Models\Connection;

class ConnectionRepository
{

    public function index()
    {
        try {
            $result = Connection::all();
        } catch (\Exception $e) {
            $result = $e;
        }
        return $result;
    }
}
