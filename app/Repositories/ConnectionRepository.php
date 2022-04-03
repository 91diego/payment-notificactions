<?php

namespace App\Repositories;

use App\Models\Connection;

class ConnectionRepository
{

    public function index($request)
    {
        try {
            $result = Connection::where('name', $request['name'])->get();//all();
        } catch (\Exception $e) {
            $result = $e;
        }
        return $result;
    }
}
