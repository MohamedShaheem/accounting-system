<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'login_at',
        'logout_at',
        'status',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to parse user agent
    public static function parseUserAgent($userAgent)
    {
        $data = [
            'device_type' => 'desktop',
            'browser' => 'Unknown',
            'platform' => 'Unknown'
        ];

        // Detect device type
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            $data['device_type'] = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            $data['device_type'] = 'mobile';
        }

        // Detect browser
        if (preg_match('/MSIE/i', $userAgent) || preg_match('/Trident/i', $userAgent)) {
            $data['browser'] = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $data['browser'] = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $data['browser'] = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $data['browser'] = 'Safari';
        } elseif (preg_match('/Opera/i', $userAgent) || preg_match('/OPR/i', $userAgent)) {
            $data['browser'] = 'Opera';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $data['browser'] = 'Edge';
        }

        // Detect platform
        if (preg_match('/linux/i', $userAgent)) {
            $data['platform'] = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $data['platform'] = 'Mac';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $data['platform'] = 'Windows';
        } elseif (preg_match('/android/i', $userAgent)) {
            $data['platform'] = 'Android';
        } elseif (preg_match('/iPad|iPhone|iPod/i', $userAgent)) {
            $data['platform'] = 'iOS';
        }

        return $data;
    }
}