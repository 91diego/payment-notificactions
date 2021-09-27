<?php

namespace App\Repositories;

use App\Models\Connection;

class ConnectionRepository
{

    protected $connectionRepository;

    public function __construct(ConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

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
