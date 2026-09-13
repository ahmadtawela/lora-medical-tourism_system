<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetPasswordRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * نقطة النهاية العامة (بلا مصادقة) التي يصل إليها المريض عبر رابط الدعوة
 * المُرسَل بالبريد/واتساب، ليعيّن كلمة مروره لأول مرة. بعد النجاح، يُسجَّل
 * دخوله تلقائيًا (تجربة استخدام أفضل من إجباره على تسجيل الدخول يدويًا فورًا).
 */
class SetPasswordController extends Controller
{
    public function __invoke(SetPasswordRequest $request): JsonResponse
    {
        $patient = Patient::where('email', $request->email)->first();

        if (! $patient || ! $patient->passwordSetupTokenIsValid($request->token)) {
            throw ValidationException::withMessages([
                'token' => ['رابط تعيين كلمة المرور غير صالح أو منتهي الصلاحية. اطلب رابطًا جديدًا من العيادة.'],
            ]);
        }

        $patient->completePasswordSetup($request->password);

        $token = $patient->createToken('patient-portal')->plainTextToken;

        return response()->json([
            'message' => 'تم تعيين كلمة المرور بنجاح',
            'patient' => new PatientResource($patient),
            'token' => $token,
        ]);
    }
}
