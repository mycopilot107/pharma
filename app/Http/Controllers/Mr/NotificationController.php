<?php

namespace App\Http\Controllers\Mr;

use App\Http\Controllers\NotificationController as BaseNotificationController;

class NotificationController extends BaseNotificationController
{
    protected function viewPrefix(): string
    {
        return 'mr';
    }
}
