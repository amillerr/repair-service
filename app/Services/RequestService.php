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
     * Перевод заявки из assigned в in_progress мастером.
     *
     * @throws RuntimeException
     */
    public function masterTake(Request $request, User $master): Request
    {
        return DB::transaction(function () use ($request, $master) {
            /** @var Request|null $fresh */
            $fresh = Request::whereKey($request->getKey())
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                throw new RuntimeException('Request not found');
            }

            if ($fresh->assigned_to !== $master->id) {
                throw new RuntimeException('Request is not assigned to this master');
            }

            if ($fresh->status !== Request::STATUS_ASSIGNED) {
                throw new RuntimeException('Only assigned requests can be taken to in_progress');
            }

            $fresh->status = Request::STATUS_IN_PROGRESS;
            $fresh->save();

            return $fresh;
        });
    }

    /**
     * Завершение заявки мастером: in_progress -> done.
     *
     * @throws RuntimeException
     */
    public function complete(Request $request, User $master): Request
    {
        return DB::transaction(function () use ($request, $master) {
            /** @var Request|null $fresh */
            $fresh = Request::whereKey($request->getKey())
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                throw new RuntimeException('Request not found');
            }

            if ($fresh->assigned_to !== $master->id) {
                throw new RuntimeException('Request is not assigned to this master');
            }

            if ($fresh->status !== Request::STATUS_IN_PROGRESS) {
                throw new RuntimeException('Only in_progress requests can be completed');
            }

            $fresh->status = Request::STATUS_DONE;
            $fresh->save();

            return $fresh;
        });
    }
}

