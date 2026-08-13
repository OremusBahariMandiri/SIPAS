<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccess
{
    public function handle(Request $request, Closure $next, string $menu, string $tipeAkses): Response
    {
        $user = $request->user();

        // Belum login → redirect ke halaman login
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Admin selalu lolos
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Cek akses
        if ($user->hasAccess($menu, $tipeAkses)) {
            return $next($request);
        }

        // Jika AJAX / fetch → kembalikan JSON 403
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.',
            ], 403);
        }

        // Tampilkan halaman 403
        abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
    }
}