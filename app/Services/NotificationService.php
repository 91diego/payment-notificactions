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

    public function index()
    {
        return $this->notificationRepository->index();
    }

    public function store($request)
    {
        return $this->notificationRepository->store($request);
    }
}
