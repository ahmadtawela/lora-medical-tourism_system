<?php

namespace App\Models;

use App\Enums\InterestLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_setup_token',
        'password_setup_expires_at',
        'phone_whatsapp',
        'birth_date',
        'country',
        'operation_type',
        'interest_level',
        'operation_date',
        'note',
    ];

    protected $hidden = [
        'password',
        'password_setup_token',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birth_date' => 'date',
            'operation_date' => 'date',
            'password_setup_expires_at' => 'datetime',
            'interest_level' => InterestLevel::class,
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function medicalReports(): HasMany
    {
        return $this->hasMany(MedicalReport::class);
    }

    /**
     * سُمّيت alerts() عمدًا وليس notifications() لتفادي تعارض الاسم مع
     * notifications()/unreadNotifications() المعرّفة داخل Notifiable trait
     * (تلك تخص جدول Laravel الداخلي لقناة 'database'، ولسنا نستخدمها هنا).
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Notification::class, 'related_patient_id');
    }

    /** إجمالي المبلغ المتبقي على المريض عبر كل فواتيره (يظهر في بطاقة "المبلغ المتبقي" ببوابة المريض) */
    public function getTotalRemainingAttribute(): float
    {
        return (float) $this->invoices()->sum('remaining_amount');
    }

    /** رقم الواتساب الذي تُرسَل إليه التنبيهات عبر قناة WhatsApp المخصّصة */
    public function routeNotificationForWhatsApp(): ?string
    {
        return $this->phone_whatsapp;
    }

    /**
     * يولّد توكن دعوة "تعيين كلمة المرور" جديدًا: يخزَّن مُشفَّرًا (hash) في القاعدة،
     * ويُرجع النسخة الصريحة (plain) لإرسالها مرة واحدة عبر البريد/واتساب داخل الرابط.
     * صالح لمدة 7 أيام.
     */
    public function generatePasswordSetupToken(): string
    {
        $plainToken = Str::random(64);

        $this->forceFill([
            'password_setup_token' => Hash::make($plainToken),
            'password_setup_expires_at' => now()->addDays(7),
        ])->save();

        return $plainToken;
    }

    /** يتحقق أن التوكن المُرسَل من الرابط صحيح ولم تنتهِ صلاحيته بعد */
    public function passwordSetupTokenIsValid(string $plainToken): bool
    {
        if (! $this->password_setup_token || ! $this->password_setup_expires_at) {
            return false;
        }

        if ($this->password_setup_expires_at->isPast()) {
            return false;
        }

        return Hash::check($plainToken, $this->password_setup_token);
    }

    /** يعيّن كلمة المرور الفعلية ويُبطل التوكن (لا يمكن استخدامه مرة أخرى) */
    public function completePasswordSetup(string $newPassword): void
    {
        $this->forceFill([
            'password' => $newPassword, // يُشفَّر تلقائيًا عبر cast 'hashed'
            'password_setup_token' => null,
            'password_setup_expires_at' => null,
        ])->save();
    }

    /** هل ما زال هذا الحساب بانتظار أن يعيّن المريض كلمة مروره لأول مرة؟ */
    public function getAwaitingPasswordSetupAttribute(): bool
    {
        return is_null($this->password) && ! is_null($this->password_setup_token);
    }
}
