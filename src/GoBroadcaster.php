<?php

namespace LaraGo\Socket;

use Illuminate\Contracts\Broadcasting\Broadcaster;

class GoBroadcaster implements Broadcaster
{
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $socketPath = "/tmp/larago.sock";
        $fp = @stream_socket_client("unix://$socketPath", $errno, $errstr);

        if (!$fp) return;

        foreach ($channels as $channel) {
            fwrite($fp, json_encode([
                'channel' => (string) $channel,
                'event'   => $event,
                'data'    => $payload
            ]));
        }
        fclose($fp);
    }

    public function auth($request) { return true; }
    public function validAuthenticationResponse($request, $result) { return []; }
}
