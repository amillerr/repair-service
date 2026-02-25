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

class DispatcherRequestController extends Controller
{
    public function __construct(
        private readonly RequestService $service
    ) {
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

        /** @var User $master */
        $master = User::query()->find($httpRequest->input('master_id'));

        if ($master->role !== User::ROLE_MASTER) {
            return response()->json(['message' => 'User is not master'], 422);
        }

        // Бизнес‑правило: нельзя назначать отменённые и завершённые заявки.
        if (in_array($request->status, [RepairRequest::STATUS_CANCELED, RepairRequest::STATUS_DONE], true)) {
            return response()->json(['message' => 'Cannot assign canceled or done request'], 409);
        }

        $request->assigned_to = $master->id;
        $request->status = RepairRequest::STATUS_ASSIGNED;
        $request->save();

        return (new RequestResource($request))->response();
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
        // Нельзя отменять уже выполненные заявки.
        if ($request->status === RepairRequest::STATUS_DONE) {
            return response()->json(['message' => 'Cannot cancel done request'], 409);
        }

        $request->status = RepairRequest::STATUS_CANCELED;
        $request->save();

        return (new RequestResource($request))->response();
    }
}

