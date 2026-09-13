<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AdminResource;
use App\Http\Resources\PatientResource;
use App\Models\Admin;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * تسجيل دخول موحّد: شاشة واحدة بدون تبويبات "لوحة الإدارة"/"بوابة المريض" -
 * النظام هو من يحدد الدور تلقائيًا من البريد/كلمة المرور المُدخلين.
 *
 * يتحقق أولًا من جدول admins، فإن لم يوجد تطابق يتحقق من جدول patients.
 * الـ endpoints المنفصلة (/admin/login و /patient/login) بقيت كما هي لأغراض
 * التوافق، ولا حاجة لحذفها.
 */
class UnifiedAuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function login(LoginRequest $request): JsonResponse
    {
        $this->ensureIsNotRateLimited($request);

        $admin = Admin::where('email', $request->email)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            RateLimiter::clear($this->throttleKey($request));

            return response()->json([
                'actor' => 'admin',
                'user' => new AdminResource($admin),
                'token' => $admin->createToken('admin-dashboard')->plainTextToken,
            ]);
        }

        $patient = Patient::where('email', $request->email)->first();

        if ($patient && $patient->awaiting_password_setup) {
            throw ValidationException::withMessages([
                'email' => ['حسابك لم يُفعَّل بعد. تحقق من بريدك الإلكتروني أو واتساب لرابط تعيين كلمة المرور.'],
            ]);
        }

        if ($patient && $patient->password && Hash::check($request->password, $patient->password)) {
            RateLimiter::clear($this->throttleKey($request));

            return response()->json([
                'actor' => 'patient',
                'user' => new PatientResource($patient),
                'token' => $patient->createToken('patient-portal')->plainTextToken,
            ]);
        }

        RateLimiter::hit($this->throttleKey($request), decaySeconds: 60);

        throw ValidationException::withMessages([
            'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة'],
        ]);
    }

    /**
     * تسجيل خروج موحّد أيضًا - لا يحتاج معرفة نوع الحساب لأن حذف التوكن
     * الحالي (currentAccessToken) يعمل بنفس الطريقة لكل من Admin وPatient.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    private function ensureIsNotRateLimited(LoginRequest $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => ["محاولات كثيرة جدًا. يُرجى المحاولة مرة أخرى بعد {$seconds} ثانية."],
        ]);
    }

    private function throttleKey(LoginRequest $request): string
    {
        return Str::lower($request->string('email')).'|'.$request->ip();
    }
}
