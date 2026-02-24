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
}

