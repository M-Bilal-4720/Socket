<?php

namespace LaraGo\Socket;

use Illuminate\Contracts\Broadcasting\Broadcaster;

class GoBroadcaster implements Broadcaster
{
    /**
     * Broadcast a message to Laravel WebSocket subscribers
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        // Get Laravel communication port from environment or default to 6001
        $host = '127.0.0.1';
        $port = (int) (getenv('LARAGO_LARAVEL_PORT') ?: 6001);

        // Create TCP socket connection to Go engine
        $fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 5);

        if (!$fp) {
            // Engine not running - fail silently in production
            if (config('app.debug')) {
                \Log::warning("LaraGo engine not responding at $host:$port");
            }
            return;
        }

        // Send each channel its message
        foreach ($channels as $channel) {
            $message = json_encode([
                'channel' => (string) $channel,
                'event'   => $event,
                'data'    => $payload
            ]);
            
            fwrite($fp, $message);
        }
        
        fclose($fp);
    }

    /**
     * Authenticate a user for private channels
     */
    public function auth($request) 
    { 
        return true; 
    }

    /**
     * Return valid authentication response
     */
    public function validAuthenticationResponse($request, $result) 
    { 
        return []; 
    }
}
