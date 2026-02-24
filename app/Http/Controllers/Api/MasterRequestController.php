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

        $query = Request::query()
            ->where('assigned_to', $master->id)
            ->orderByDesc('created_at');

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

    public function take(HttpRequest $request, Request $repairRequest): JsonResponse
    {
        /** @var User $master */
        $master = $request->user();

        try {
            $updated = $this->service->masterTake($repairRequest, $master);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new RequestResource($updated))->response();
    }

    public function complete(HttpRequest $request, Request $repairRequest): JsonResponse
    {
        /** @var User $master */
        $master = $request->user();

        try {
            $updated = $this->service->complete($repairRequest, $master);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new RequestResource($updated))->response();
    }
}

