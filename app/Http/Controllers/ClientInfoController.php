<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientInfoController extends Controller
{
    public function getInfo(Request $request)
    {
        $macAddress = null;
        $ipAddress = $request->ip();

        // Get MAC address from various possible headers
        $possibleHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($possibleHeaders as $header) {
            if ($request->server($header)) {
                $ipAddress = $request->server($header);
                break;
            }
        }

        // Try to get MAC from ARP table (works when running on same network)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('arp -a ' . $ipAddress, $output);
            foreach ($output as $line) {
                if (strpos($line, $ipAddress) !== false) {
                    preg_match('/([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})/', $line, $matches);
                    if (isset($matches[0])) {
                        $macAddress = strtoupper($matches[0]);
                    }
                }
            }
        } else {
            exec('arp -n ' . $ipAddress, $output);
            foreach ($output as $line) {
                if (strpos($line, $ipAddress) !== false) {
                    preg_match('/([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})/', $line, $matches);
                    if (isset($matches[0])) {
                        $macAddress = strtoupper($matches[0]);
                    }
                }
            }
        }

        return response()->json([
            'mac_address' => $macAddress,
            'ip_address' => $ipAddress
        ]);
    }
}
