<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * تسجيل دخول/خروج "لوحة الإدارة" - يطابق تبويب "لوحة الإدارة" في شاشة تسجيل الدخول.
 *
 * محمي بحد أقصى 5 محاولات فاشلة لكل (بريد إلكتروني + IP)، بالإضافة إلى
 * throttle:10,1 على مستوى الراوت (انظر routes/api.php) كخط دفاع أول أعم.
 */
class AdminAuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function login(LoginRequest $request): JsonResponse
    {
        $this->ensureIsNotRateLimited($request);

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            RateLimiter::hit($this->throttleKey($request), decaySeconds: 60);

            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة'],
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $token = $admin->createToken('admin-dashboard')->plainTextToken;

        return response()->json([
            'admin' => new AdminResource($admin),
            'token' => $token,
        ]);
    }

    /** يمنع محاولات تخمين كلمة المرور المتكررة على نفس الحساب من نفس الجهاز/الشبكة */
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

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }
}
