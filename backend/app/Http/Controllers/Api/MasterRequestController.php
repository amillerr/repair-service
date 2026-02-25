<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestAuditLogResource;
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

    public function audit(HttpRequest $httpRequest, RepairRequest $request): JsonResponse
    {
        /** @var User $master */
        $master = $httpRequest->user();

        if ($request->assigned_to !== $master->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $logs = $request->auditLogs()->orderByDesc('created_at')->get();

        return RequestAuditLogResource::collection($logs)->response();
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

        try {
            $updated = $this->service->masterTake($request, $master);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new RequestResource($updated))->response();
    }

    public function complete(HttpRequest $httpRequest, RepairRequest $request): JsonResponse
    {
        /** @var User $master */
        $master = $httpRequest->user();

        try {
            $updated = $this->service->complete($request, $master);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new RequestResource($updated))->response();
    }
}

