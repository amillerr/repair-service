<?php

namespace App\Services;

use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RequestService
{
    /**
     * Действие «взять в работу» с защитой от race condition (новая заявка).
     *
     * @throws RuntimeException
     */
    public function takeInWork(Request $request, User $master): Request
    {
        return DB::transaction(function () use ($request, $master) {
            // Обновляем состояние модели внутри транзакции
            $request->refresh();

            if ($request->status !== Request::STATUS_NEW) {
                throw new RuntimeException('Request already taken or closed');
            }

            $request->status = Request::STATUS_IN_PROGRESS;
            $request->assigned_to = $master->id;
            $request->save();

            return $request;
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
            $request->refresh();

            if (in_array($request->status, [Request::STATUS_CANCELED, Request::STATUS_DONE], true)) {
                throw new RuntimeException('Cannot assign canceled or done request');
            }

            $request->assigned_to = $master->id;
            $request->status = Request::STATUS_ASSIGNED;
            $request->save();

            return $request;
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
            $request->refresh();

            if ($request->status === Request::STATUS_DONE) {
                throw new RuntimeException('Cannot cancel done request');
            }

            $request->status = Request::STATUS_CANCELED;
            $request->save();

            return $request;
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
            $request->refresh();

            if ($request->assigned_to !== $master->id) {
                throw new RuntimeException('Request is not assigned to this master');
            }

            if ($request->status !== Request::STATUS_ASSIGNED) {
                throw new RuntimeException('Only assigned requests can be taken to in_progress');
            }

            $request->status = Request::STATUS_IN_PROGRESS;
            $request->save();

            return $request;
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
            $request->refresh();

            if ($request->assigned_to !== $master->id) {
                throw new RuntimeException('Request is not assigned to this master');
            }

            if ($request->status !== Request::STATUS_IN_PROGRESS) {
                throw new RuntimeException('Only in_progress requests can be completed');
            }

            $request->status = Request::STATUS_DONE;
            $request->save();

            return $request;
        });
    }
}

