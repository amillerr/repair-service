<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Models\Request;
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
        $query = Request::query()->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $allowed = [
                Request::STATUS_NEW,
                Request::STATUS_ASSIGNED,
                Request::STATUS_IN_PROGRESS,
                Request::STATUS_DONE,
                Request::STATUS_CANCELED,
            ];

            if (! in_array($status, $allowed, true)) {
                return response()->json(['message' => 'Invalid status'], 422);
            }

            $query->where('status', $status);
        }

        return RequestResource::collection($query->get())->response();
    }

    public function assign(HttpRequest $request, Request $repairRequest): JsonResponse
    {
        $request->validate([
            'master_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        /** @var User $master */
        $master = User::query()->find($request->input('master_id'));

        if ($master->role !== User::ROLE_MASTER) {
            return response()->json(['message' => 'User is not master'], 422);
        }

        try {
            $updated = $this->service->assign($repairRequest, $master);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new RequestResource($updated))->response();
    }

    public function cancel(HttpRequest $request, Request $repairRequest): JsonResponse
    {
        try {
            $updated = $this->service->cancel($repairRequest);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new RequestResource($updated))->response();
    }
}

