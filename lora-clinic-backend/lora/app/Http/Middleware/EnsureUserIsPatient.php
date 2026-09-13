<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يتحقق أن صاحب التوكن الحالي هو Patient وليس Admin.
 */
class EnsureUserIsPatient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Patient) {
            return response()->json([
                'message' => 'غير مصرح لك بالوصول - هذا المسار مخصص للمرضى فقط',
            ], 403);
        }

        return $next($request);
    }
}
