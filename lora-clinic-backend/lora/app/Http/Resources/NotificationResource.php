<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Notification */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'severity' => $this->type->severity(),
            'title' => $this->title,
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient->id,
                'name' => $this->patient->name,
            ]),
            'related_invoice_id' => $this->related_invoice_id,
            'related_report_id' => $this->related_report_id,
            'event_date' => $this->event_date?->toDateString(),
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}
