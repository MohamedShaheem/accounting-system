<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginHistory::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by device type
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('browser', 'like', "%{$search}%")
                  ->orWhere('platform', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $loginHistories = $query->orderBy('login_at', 'desc')->paginate(15);
        $users = User::orderBy('name')->get();

        return view('login-history.index', compact('loginHistories', 'users'));
    }

    public function userHistory($userId)
    {
        $user = User::findOrFail($userId);
        $loginHistories = LoginHistory::where('user_id', $userId)
            ->orderBy('login_at', 'desc')
            ->paginate(15);

        return view('login-history.user-history', compact('user', 'loginHistories'));
    }

    public function destroy($id)
    {
        $loginHistory = LoginHistory::findOrFail($id);
        $loginHistory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Login history deleted successfully.'
        ]);
    }

    public function clearOldRecords(Request $request)
    {
        $days = $request->input('days', 90); // Default 90 days
        
        $deleted = LoginHistory::where('login_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} old login records."
        ]);
    }
}