<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'title',
        'related_patient_id',
        'related_invoice_id',
        'related_report_id',
        'event_date',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'event_date' => 'date',
            'is_read' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'related_patient_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(MedicalReport::class, 'related_report_id');
    }
}
