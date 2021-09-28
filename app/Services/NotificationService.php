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

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return $this->notificationRepository->index();
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
        // // TODO
    }
}
