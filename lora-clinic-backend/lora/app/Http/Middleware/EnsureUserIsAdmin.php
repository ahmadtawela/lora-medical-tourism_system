<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يتحقق أن صاحب التوكن الحالي هو Admin وليس Patient.
 * ضروري لأن Sanctum يخزن كل التوكنات (لكل من الأدمن والمريض) في نفس الجدول
 * polymorphically، لذلك auth:sanctum وحده لا يميّز بين النوعين.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Admin) {
            return response()->json([
                'message' => 'غير مصرح لك بالوصول - هذا المسار مخصص لفريق الإدارة فقط',
            ], 403);
        }

        return $next($request);
    }
}
