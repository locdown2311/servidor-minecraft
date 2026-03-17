<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Server;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'servers' => Server::count(),
            'servers_running' => Server::where('status', 'running')->count(),
            'revenue' => Payment::where('status', 'paid')->sum('amount'),
        ];

        $recentServers = Server::with(['user', 'plan'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.index', compact('stats', 'recentServers'));
    }

    public function servers(): View
    {
        $servers = Server::with(['user', 'plan'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.servers', compact('servers'));
    }

    public function users(): View
    {
        $users = User::withCount('servers')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function plans(): View
    {
        $plans = Plan::withCount('servers')
            ->orderBy('sort_order')
            ->get();

        return view('admin.plans', compact('plans'));
    }
}
