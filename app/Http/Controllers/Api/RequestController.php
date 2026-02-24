<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Resources\RequestResource;
use App\Models\Request as RepairRequest;
use App\Models\User;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class RequestController extends Controller
{
    public function __construct(
        private readonly RequestService $service
    ) {
    }

    public function store(StoreRequestRequest $request): JsonResponse
    {
        $repairRequest = RepairRequest::query()->create($request->validated());

        return (new RequestResource($repairRequest))
            ->response()
            ->setStatusCode(201);
    }

    public function takeInWork(Request $httpRequest, RepairRequest $request): JsonResponse
    {
        /** @var User $master — уже проверен middleware role:master */
        $master = $httpRequest->user();

        try {
            $updated = $this->service->takeInWork($request, $master);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'id' => $updated->id,
            'status' => $updated->status,
            'assigned_to' => $updated->assigned_to,
        ]);
    }
}

