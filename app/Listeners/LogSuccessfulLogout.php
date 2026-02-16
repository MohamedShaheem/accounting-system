<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\LoginHistory;

class LogSuccessfulLogout
{
    public function handle(Logout $event)
    {
        $user = $event->user;
        
        if ($user) {
            // Update the latest login record with logout time
            $latestLogin = LoginHistory::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();

            if ($latestLogin) {
                $latestLogin->update([
                    'logout_at' => now()
                ]);
            }
        }
    }
}