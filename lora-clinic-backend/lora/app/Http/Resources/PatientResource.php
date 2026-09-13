<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Patient */
class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'initials' => $this->initials(),
            'email' => $this->email,
            'phone_whatsapp' => $this->phone_whatsapp,
            'birth_date' => $this->birth_date?->toDateString(),
            'country' => $this->country,
            'operation_type' => $this->operation_type,
            'operation_date' => $this->operation_date?->toDateString(),
            'interest_level' => $this->interest_level->value,
            'interest_level_label' => $this->interest_level->label(),
            'note' => $this->note,
            // هل ما زال المريض بانتظار تعيين كلمة مروره لأول مرة عبر رابط الدعوة؟
            'awaiting_password_setup' => $this->awaiting_password_setup,
            'total_remaining' => $this->when(
                $this->relationLoaded('invoices') || $request->routeIs('*.patients.show'),
                fn () => (float) $this->total_remaining
            ),
            'registered_at' => $this->created_at?->toDateString(),
        ];
    }

    /** الحرف الأول من كل كلمتين في الاسم لعرضه داخل شارة دائرية (مثل "أع" لأحمد العمري) */
    private function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $second = mb_substr($parts[1] ?? '', 0, 1);

        return $first.$second;
    }
}
