<?php

namespace App\Http\Resources;

use App\Models\Request as RepairRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    private const STATUS_LABELS = [
        RepairRequest::STATUS_NEW => 'Новая',
        RepairRequest::STATUS_ASSIGNED => 'Назначена',
        RepairRequest::STATUS_IN_PROGRESS => 'В работе',
        RepairRequest::STATUS_DONE => 'Выполнена',
        RepairRequest::STATUS_CANCELED => 'Отменена',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'problem_text' => $this->problem_text,
            'status' => $this->status,
            'status_label' => self::STATUS_LABELS[$this->status] ?? $this->status,
            'assigned_to' => $this->assigned_to,
            'master_name' => $this->whenLoaded('assignedTo', fn () => $this->assignedTo?->name),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
