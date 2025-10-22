<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyLocalIP
{
    // Your LAN subnet
    protected string $allowedSubnet = '172.20.10.';

public function handle(Request $request, Closure $next)
{
    $ip = $request->getClientIp();

    if ($ip === '127.0.0.1' || $ip === '::1') {
        // Force the LAN IP for testing
        $ip = '172.20.10.5';
    }

    if (!str_starts_with($ip, $this->allowedSubnet)) {
        return response()->json([
            'error' => 'Access denied',
            'your_ip' => $ip,
        ], 403);
    }

    return $next($request);
}


    // Get LAN IP of this machine
    private function getLocalLANIP(): ?string
    {
        $ifconfig = shell_exec('ipconfig'); // Windows only
        if ($ifconfig) {
            preg_match('/IPv4 Address.*?:\s*([\d\.]+)/', $ifconfig, $matches);
            if (isset($matches[1])) {
                return $matches[1]; // e.g., 172.20.10.5
            }
        }
        return null;
    }
}
