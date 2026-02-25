<?php

namespace App\Services;

use App\Models\Request;
use App\Models\RequestAuditLog;
use App\Models\User;

class RequestAuditService
{
    public function logCreated(Request $request, ?User $user = null): void
    {
        RequestAuditLog::query()->create([
            'request_id' => $request->id,
            'action' => RequestAuditLog::ACTION_CREATED,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'from_status' => null,
            'to_status' => Request::STATUS_NEW,
            'metadata' => null,
        ]);
    }

    public function logAssigned(Request $request, User $dispatcher, User $master, string $fromStatus): void
    {
        RequestAuditLog::query()->create([
            'request_id' => $request->id,
            'action' => RequestAuditLog::ACTION_ASSIGNED,
            'user_id' => $dispatcher->id,
            'user_name' => $dispatcher->name,
            'from_status' => $fromStatus,
            'to_status' => Request::STATUS_ASSIGNED,
            'metadata' => ['master_id' => $master->id, 'master_name' => $master->name],
        ]);
    }

    public function logCanceled(Request $request, User $dispatcher, string $fromStatus): void
    {
        RequestAuditLog::query()->create([
            'request_id' => $request->id,
            'action' => RequestAuditLog::ACTION_CANCELED,
            'user_id' => $dispatcher->id,
            'user_name' => $dispatcher->name,
            'from_status' => $fromStatus,
            'to_status' => Request::STATUS_CANCELED,
            'metadata' => null,
        ]);
    }

    public function logTaken(Request $request, User $master, string $fromStatus): void
    {
        RequestAuditLog::query()->create([
            'request_id' => $request->id,
            'action' => RequestAuditLog::ACTION_TAKEN,
            'user_id' => $master->id,
            'user_name' => $master->name,
            'from_status' => $fromStatus,
            'to_status' => Request::STATUS_IN_PROGRESS,
            'metadata' => null,
        ]);
    }

    public function logCompleted(Request $request, User $master, string $fromStatus): void
    {
        RequestAuditLog::query()->create([
            'request_id' => $request->id,
            'action' => RequestAuditLog::ACTION_COMPLETED,
            'user_id' => $master->id,
            'user_name' => $master->name,
            'from_status' => $fromStatus,
            'to_status' => Request::STATUS_DONE,
            'metadata' => null,
        ]);
    }

    public function logTakenInWork(Request $request, User $master): void
    {
        RequestAuditLog::query()->create([
            'request_id' => $request->id,
            'action' => RequestAuditLog::ACTION_TAKEN,
            'user_id' => $master->id,
            'user_name' => $master->name,
            'from_status' => Request::STATUS_NEW,
            'to_status' => Request::STATUS_IN_PROGRESS,
            'metadata' => ['direct_take' => true],
        ]);
    }
}
