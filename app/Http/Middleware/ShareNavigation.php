<?php

namespace App\Http\Middleware;

use App\Services\NotificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares the role-aware notification feed with every view so the bell dropdown
 * and its numeric badge stay consistent without each controller wiring it up.
 */
class ShareNavigation
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(Request $request, Closure $next): Response
    {
        $items = $request->expectsJson()
            ? collect()
            : $this->notifications->forUser($request->user());

        View::share('navNotifications', $items);
        View::share('navNotificationCount', $items->sum('count'));

        return $next($request);
    }
}
