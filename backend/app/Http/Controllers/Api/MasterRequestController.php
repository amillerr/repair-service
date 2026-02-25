<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Models\Request as RepairRequest;
use App\Models\User;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use RuntimeException;

class MasterRequestController extends Controller
{
    public function __construct(
        private readonly RequestService $service
    ) {
    }

    public function index(HttpRequest $request): JsonResponse
    {
        /** @var User $master */
        $master = $request->user();

        $query = RepairRequest::query()
            ->with('assignedTo')
            ->where('assigned_to', $master->id)
            ->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $allowed = [
                RepairRequest::STATUS_NEW,
                RepairRequest::STATUS_ASSIGNED,
                RepairRequest::STATUS_IN_PROGRESS,
                RepairRequest::STATUS_DONE,
                RepairRequest::STATUS_CANCELED,
            ];

            if (! in_array($status, $allowed, true)) {
                return response()->json(['message' => 'Invalid status'], 422);
            }

            $query->where('status', $status);
        }

        return RequestResource::collection($query->get())->response();
    }

    public function take(HttpRequest $httpRequest, RepairRequest $request): JsonResponse
    {
        /** @var User $master */
        $master = $httpRequest->user();

        // Заявка должна быть назначена этому мастеру и иметь статус ASSIGNED.
        if ($request->assigned_to !== $master->id || $request->status !== RepairRequest::STATUS_ASSIGNED) {
            return response()->json(['message' => 'Request cannot be taken'], 409);
        }

        $request->status = RepairRequest::STATUS_IN_PROGRESS;
        $request->save();

        return (new RequestResource($request))->response();
    }

    public function complete(HttpRequest $httpRequest, RepairRequest $request): JsonResponse
    {
        /** @var User $master */
        $master = $httpRequest->user();

        // Завершать может только тот мастер, кому назначена заявка, и только из in_progress.
        if ($request->assigned_to !== $master->id || $request->status !== RepairRequest::STATUS_IN_PROGRESS) {
            return response()->json(['message' => 'Request cannot be completed'], 409);
        }

        $request->status = RepairRequest::STATUS_DONE;
        $request->save();

        return (new RequestResource($request))->response();
    }
}

