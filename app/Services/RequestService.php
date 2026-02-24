<?php

namespace App\Services;

use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RequestService
{
    /**
     * Действие «взять в работу» с защитой от race condition.
     *
     * @throws RuntimeException
     */
    public function takeInWork(Request $request, User $master): Request
    {
        return DB::transaction(function () use ($request, $master) {
            /** @var Request|null $fresh */
            $fresh = Request::whereKey($request->getKey())
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                throw new RuntimeException('Request not found');
            }

            if ($fresh->status !== Request::STATUS_NEW) {
                throw new RuntimeException('Request already taken or closed');
            }

            $fresh->status = Request::STATUS_IN_PROGRESS;
            $fresh->assigned_to = $master->id;
            $fresh->save();

            return $fresh;
        });
    }

    /**
     * Назначение мастера диспетчером.
     *
     * @throws RuntimeException
     */
    public function assign(Request $request, User $master): Request
    {
        return DB::transaction(function () use ($request, $master) {
            /** @var Request|null $fresh */
            $fresh = Request::whereKey($request->getKey())
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                throw new RuntimeException('Request not found');
            }

            if (in_array($fresh->status, [Request::STATUS_CANCELED, Request::STATUS_DONE], true)) {
                throw new RuntimeException('Cannot assign canceled or done request');
            }

            $fresh->assigned_to = $master->id;
            $fresh->status = Request::STATUS_ASSIGNED;
            $fresh->save();

            return $fresh;
        });
    }

    /**
     * Отмена заявки диспетчером.
     *
     * @throws RuntimeException
     */
    public function cancel(Request $request): Request
    {
        return DB::transaction(function () use ($request) {
            /** @var Request|null $fresh */
            $fresh = Request::whereKey($request->getKey())
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                throw new RuntimeException('Request not found');
            }

            if ($fresh->status === Request::STATUS_DONE) {
                throw new RuntimeException('Cannot cancel done request');
            }

            $fresh->status = Request::STATUS_CANCELED;
            $fresh->save();

            return $fresh;
        });
    }
}

