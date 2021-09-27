<?php

namespace App\Services;

use App\Repositories\ConnectionRepository;

class ConnectionService
{
    protected $connectionRepository;

    public function __construct(ConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    public function index()
    {
        return $this->connectionRepository->index();
    }
}
