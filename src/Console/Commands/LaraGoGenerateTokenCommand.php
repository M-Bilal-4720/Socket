<?php

namespace LaraGo\Socket\Console\Commands;

use Illuminate\Console\Command;

class LaraGoGenerateTokenCommand extends Command
{
    protected $signature = 'larago:token {--user-id=1 : User ID for the token} {--expires=3600 : Token expiration time in seconds}';
    protected $description = 'Generate a JWT token for private LaraGo connections';

    public function handle()
    {
        $userId = $this->option('user-id');
        $expiresIn = $this->option('expires');
        $secret = config('app.key') ?: 'larago-secret-key';

        // Remove 'base64:' prefix if present
        if (strpos($secret, 'base64:') === 0) {
            $secret = base64_decode(substr($secret, 7));
        }

        $now = time();
        $payload = [
            'iat' => $now,
            'exp' => $now + $expiresIn,
            'user_id' => $userId,
            'app' => 'larago'
        ];

        // Create a simple JWT token (header.payload.signature)
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payloadEncoded", $secret, true);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        
        $token = "$header.$payloadEncoded.$signatureEncoded";
        
        $this->info('✅ JWT Token generated successfully!');
        $this->newLine();
        $this->line('Token: <info>' . $token . '</info>');
        $this->newLine();
        $this->line('Token Details:');
        $this->line('  • User ID: <comment>' . $userId . '</comment>');
        $this->line('  • Expires in: <comment>' . $expiresIn . ' seconds</comment>');
        $this->line('  • Expires at: <comment>' . date('Y-m-d H:i:s', $now + $expiresIn) . '</comment>');
        $this->newLine();
        $this->line('Usage:');
        $this->line('  1. Copy the token above');
        $this->line('  2. Open the test page: <comment>http://localhost:8000/larago-test</comment>');
        $this->line('  3. Change Connection Mode to <comment>Private</comment>');
        $this->line('  4. Paste the token in the JWT Token field');
        $this->line('  5. Click Subscribe to connect');

        return 0;
    }
}
