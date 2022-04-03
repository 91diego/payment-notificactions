<?php

namespace App\Services;

use App\Repositories\NotificationRepository;

class NotificationService
{
    protected $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function index($request)
    {
        return $this->notificationRepository->index($request);
    }

    public function store($request)
    {
        return $this->notificationRepository->store($request);
    }
}
