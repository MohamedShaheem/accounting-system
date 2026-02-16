<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginHistory;

class LogSuccessfulLogin
{
    public function handle(Login $event)
    {
        $user = $event->user;
        $request = request();

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
            'status' => 'success',
        ]);
    }

}