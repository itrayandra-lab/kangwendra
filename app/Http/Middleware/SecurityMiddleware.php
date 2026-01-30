<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $this->checkPathTraversal($request);
        $this->checkShellInjection($request);
        $this->checkSuspiciousFiles($request);
        return $next($request);
    }

    private function checkPathTraversal($request)
    {
        foreach ($request->all() as $key => $value) {
            if (is_string($value) && preg_match('/\.\.\/|\.\.\\\\|%2e%2e%2f/i', $value)) {
                Log::channel('security')->critical('Path traversal attempt', [
                    'field' => $key, 'value' => $value, 'ip' => $request->ip()
                ]);
                abort(403, 'Path traversal detected');
            }
        }
    }

    private function checkShellInjection($request)
    {
        $patterns = ['/\b(exec|system|shell_exec|passthru|eval)\s*\(/i', '/;\s*(rm|del|unlink)/i'];
        foreach ($request->all() as $key => $value) {
            if (is_string($value) && !in_array($key, ['_token', '_method'])) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        Log::channel('security')->critical('Shell injection attempt', [
                            'field' => $key, 'ip' => $request->ip()
                        ]);
                        abort(403, 'Shell injection detected');
                    }
                }
            }
        }
    }

    private function checkSuspiciousFiles($request)
    {
        $files = array_merge(
            $request->file('file') ? [$request->file('file')] : [],
            $request->file('image') ? [$request->file('image')] : []
        );

        foreach ($files as $file) {
            if ($file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $dangerous = ['php', 'exe', 'sh', 'bat', 'cmd', 'js', 'py'];
                if (in_array($ext, $dangerous)) {
                    Log::channel('security')->critical('Dangerous file upload', [
                        'filename' => $file->getClientOriginalName(), 'ip' => $request->ip()
                    ]);
                    abort(403, 'Dangerous file detected');
                }
            }
        }
    }
}