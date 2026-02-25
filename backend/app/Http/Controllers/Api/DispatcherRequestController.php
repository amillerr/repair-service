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

class DispatcherRequestController extends Controller
{
    public function __construct(
        private readonly RequestService $service
    ) {
    }

    public function audit(HttpRequest $httpRequest, RepairRequest $request): JsonResponse
    {
        $logs = $request->auditLogs()->orderByDesc('created_at')->get();

        return RequestAuditLogResource::collection($logs)->response();
    }

    public function index(HttpRequest $request): JsonResponse
    {
        $query = RepairRequest::query()
            ->with('assignedTo')
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

    public function assign(HttpRequest $httpRequest, RepairRequest $request): JsonResponse
    {
        $httpRequest->validate([
            'master_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        /** @var User $dispatcher */
        $dispatcher = $httpRequest->user();

        /** @var User $master */
        $master = User::query()->find($httpRequest->input('master_id'));

        if ($master->role !== User::ROLE_MASTER) {
            return response()->json(['message' => 'User is not master'], 422);
        }

        try {
            $updated = $this->service->assign($request, $dispatcher, $master);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new RequestResource($updated))->response();
    }

    public function masters(): JsonResponse
    {
        $masters = User::query()
            ->where('role', User::ROLE_MASTER)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $masters]);
    }

    public function clients(): JsonResponse
    {
        $clients = RepairRequest::query()
            ->selectRaw('client_name, phone, MAX(address) as address, COUNT(*) as requests_count')
            ->groupBy('client_name', 'phone')
            ->orderBy('client_name')
            ->get();

        return response()->json(['data' => $clients]);
    }

    public function cancel(HttpRequest $httpRequest, RepairRequest $request): JsonResponse
    {
        /** @var User $dispatcher */
        $dispatcher = $httpRequest->user();

        try {
            $updated = $this->service->cancel($request, $dispatcher);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new RequestResource($updated))->response();
    }
}

