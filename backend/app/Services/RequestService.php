<?php

namespace App\Services;

use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RequestService
{
    public function __construct(
        private readonly RequestAuditService $audit
    ) {
    }

    /**
     * Действие «взять в работу» с защитой от race condition (новая заявка).
     * Использует SELECT ... FOR UPDATE для блокировки строки.
     *
     * @throws RuntimeException
     */
    public function takeInWork(Request $request, User $master): Request
    {
        return DB::transaction(function () use ($request, $master) {
            $locked = Request::query()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                throw new RuntimeException('Request not found');
            }

            if ($locked->status !== Request::STATUS_NEW) {
                throw new RuntimeException('Request already taken or closed');
            }

            $locked->status = Request::STATUS_IN_PROGRESS;
            $locked->assigned_to = $master->id;
            $locked->save();

            $this->audit->logTakenInWork($locked, $master);

            return $locked;
        });
    }

    /**
     * Назначение мастера диспетчером.
     *
     * @throws RuntimeException
     */
    public function assign(Request $request, User $dispatcher, User $master): Request
    {
        return DB::transaction(function () use ($request, $dispatcher, $master) {
            $locked = Request::query()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                throw new RuntimeException('Request not found');
            }

            if (in_array($locked->status, [Request::STATUS_CANCELED, Request::STATUS_DONE], true)) {
                throw new RuntimeException('Cannot assign canceled or done request');
            }

            $fromStatus = $locked->status;
            $locked->assigned_to = $master->id;
            $locked->status = Request::STATUS_ASSIGNED;
            $locked->save();

            $this->audit->logAssigned($locked, $dispatcher, $master, $fromStatus);

            return $locked;
        });
    }

    /**
     * Отмена заявки диспетчером.
     *
     * @throws RuntimeException
     */
    public function cancel(Request $request, User $dispatcher): Request
    {
        return DB::transaction(function () use ($request, $dispatcher) {
            $locked = Request::query()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                throw new RuntimeException('Request not found');
            }

            if ($locked->status === Request::STATUS_DONE) {
                throw new RuntimeException('Cannot cancel done request');
            }

            $fromStatus = $locked->status;
            $locked->status = Request::STATUS_CANCELED;
            $locked->save();

            $this->audit->logCanceled($locked, $dispatcher, $fromStatus);

            return $locked;
        });
    }

    /**
     * Перевод заявки из assigned в in_progress мастером.
     * Использует SELECT ... FOR UPDATE для защиты от race condition.
     *
     * @throws RuntimeException
     */
    public function masterTake(Request $request, User $master): Request
    {
        return DB::transaction(function () use ($request, $master) {
            $locked = Request::query()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                throw new RuntimeException('Request not found');
            }

            if ($locked->assigned_to !== $master->id) {
                throw new RuntimeException('Request is not assigned to this master');
            }

            if ($locked->status !== Request::STATUS_ASSIGNED) {
                throw new RuntimeException('Only assigned requests can be taken to in_progress');
            }

            $fromStatus = $locked->status;
            $locked->status = Request::STATUS_IN_PROGRESS;
            $locked->save();

            $this->audit->logTaken($locked, $master, $fromStatus);

            return $locked;
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
            $locked = Request::query()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                throw new RuntimeException('Request not found');
            }

            if ($locked->assigned_to !== $master->id) {
                throw new RuntimeException('Request is not assigned to this master');
            }

            if ($locked->status !== Request::STATUS_IN_PROGRESS) {
                throw new RuntimeException('Only in_progress requests can be completed');
            }

            $fromStatus = $locked->status;
            $locked->status = Request::STATUS_DONE;
            $locked->save();

            $this->audit->logCompleted($locked, $master, $fromStatus);

            return $locked;
        });
    }
}
