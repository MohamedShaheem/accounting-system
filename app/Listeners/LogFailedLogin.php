<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\LoginHistory;
use App\Models\User;

class LogFailedLogin
{
    public function handle(Failed $event)
    {
        $request = request();
        
        // Try to find user by email
        $user = User::where('email', $event->credentials['email'] ?? '')->first();

        if ($user) {
            $userAgent = $request->userAgent();
            $parsedData = LoginHistory::parseUserAgent($userAgent);

            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'device_type' => $parsedData['device_type'],
                'browser' => $parsedData['browser'],
                'platform' => $parsedData['platform'],
                'login_at' => now(),
                'status' => 'failed',
            ]);
        }
    }
}