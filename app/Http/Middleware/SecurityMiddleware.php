<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->checkPathTraversal($request);
        $this->checkShellInjection($request);
        $this->checkSuspiciousFiles($request);
        
        return $next($request);
    }
    
    private function checkPathTraversal(Request $request)
    {
        $inputs = $request->all();
        
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                if (preg_match('/\.\.\/|\.\.\\\\|%2e%2e%2f|%2e%2e%5c/i', $value)) {
                    Log::channel('security')->critical('Path traversal attempt detected', [
                        'field' => $key,
                        'value' => $value,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'url' => $request->fullUrl(),
                        'timestamp' => now(),
                    ]);
                    
                    abort(403, 'Path traversal detected');
                }
            }
        }
    }
    
    private function checkShellInjection(Request $request)
    {
        $inputs = $request->all();
        $dangerousPatterns = [
            '/\b(exec|system|shell_exec|passthru|eval|base64_decode)\s*\(/i',
            '/\b(rm|del|unlink)\s+/i',
            '/;\s*(rm|del|unlink|exec|system)/i',
            '/\|\s*(rm|del|unlink|exec|system)/i',
            '/&&\s*(rm|del|unlink|exec|system)/i',
            '/`[^`]*`/i',
            '/\$\([^)]*\)/i',
        ];
        
        foreach ($inputs as $key => $value) {
            if (is_string($value) && !in_array($key, ['_token', '_method'])) {
                foreach ($dangerousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        Log::channel('security')->critical('Shell injection attempt detected', [
                            'field' => $key,
                            'value' => $value,
                            'pattern' => $pattern,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'url' => $request->fullUrl(),
                            'timestamp' => now(),
                        ]);
                        
                        abort(403, 'Shell injection detected');
                    }
                }
            }
        }
    }
    
    private function checkSuspiciousFiles(Request $request)
    {
        $allFiles = [];
        
        foreach ($request->allFiles() as $key => $files) {
            if (is_array($files)) {
                foreach ($files as $file) {
                    if ($file) {
                        $allFiles[] = $file;
                    }
                }
            } else {
                if ($files) {
                    $allFiles[] = $files;
                }
            }
        }
        
        foreach ($allFiles as $file) {
            if ($file && method_exists($file, 'getClientOriginalExtension')) {
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = $file->getClientOriginalName();
                
                $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'exe', 'sh', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar', 'pl', 'py', 'rb'];
                
                if (in_array($extension, $dangerousExtensions)) {
                    Log::channel('security')->critical('Dangerous file upload attempt', [
                        'filename' => $filename,
                        'extension' => $extension,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'timestamp' => now(),
                    ]);
                    
                    abort(403, 'Dangerous file type detected');
                }
                
                if (preg_match('/\.(htaccess|htpasswd|ini|conf|config)$/i', $filename)) {
                    Log::channel('security')->critical('System file upload attempt', [
                        'filename' => $filename,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'timestamp' => now(),
                    ]);
                    
                    abort(403, 'System file upload detected');
                }
            }
        }
    }
}