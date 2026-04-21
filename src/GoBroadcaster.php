<?php

namespace LaraGo\Socket;

use Illuminate\Broadcasting\Broadcasters\Broadcaster as BaseBroadcaster;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GoBroadcaster extends BaseBroadcaster
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
        $channelName = $request->channel_name ?? null;

        if (empty($channelName)) {
            throw new AccessDeniedHttpException('Channel not provided.');
        }

        return $this->verifyUserCanAccessChannel($request, $channelName);
    }

    /**
     * Return valid authentication response
     */
    public function validAuthenticationResponse($request, $result)
    {
        return ['authorized' => (bool) $result];
    }
}
