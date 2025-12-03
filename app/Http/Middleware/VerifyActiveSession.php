<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de verificación de sesión activa
 * Previene acceso no autorizado después de logout
 */
class VerifyActiveSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            // Limpiar cualquier sesión residual
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Si es AJAX, devolver JSON con error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Sesión expirada',
                    'message' => 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.',
                    'redirect' => route('login')
                ], 401);
            }
            
            // Redirigir al login con mensaje
            return redirect()->route('login')
                ->with('error', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }
        
        // 2. Verificar que el usuario esté activo
        $user = Auth::user();
        if (!$user->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Usuario desactivado',
                    'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
                    'redirect' => route('login')
                ], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
        }
        
        // 3. Verificar timeout de inactividad (30 minutos)
        $lastActivity = session('last_activity');
        $timeout = config('session.lifetime', 120) * 60; // En segundos
        
        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Sesión expirada por inactividad',
                    'message' => 'Tu sesión ha expirado por inactividad.',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'Tu sesión ha expirado por inactividad.');
        }
        
        // Actualizar última actividad
        session(['last_activity' => time()]);
        
        $response = $next($request);
        
        // 4. Agregar headers de seguridad adicionales para rutas autenticadas
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        return $response;
    }
}
