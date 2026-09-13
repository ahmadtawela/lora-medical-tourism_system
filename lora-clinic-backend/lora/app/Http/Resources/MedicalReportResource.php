<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\MedicalReport */
class MedicalReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'diagnosis_name' => $this->diagnosis_name,
            'patient' => [
                'id' => $this->patient->id,
                'name' => $this->patient->name,
            ],
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_url' => Storage::disk('public')->url($this->file_path),
            'description' => $this->description,
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}
