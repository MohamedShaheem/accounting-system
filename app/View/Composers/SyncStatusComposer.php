<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\SyncDetail;
use Carbon\Carbon;

class SyncStatusComposer
{
    public function compose(View $view)
    {
        $hasSyncedToday = SyncDetail::whereDate('synced_at', Carbon::today())->exists();

        $view->with('hasSyncedToday', $hasSyncedToday);
    }
}
