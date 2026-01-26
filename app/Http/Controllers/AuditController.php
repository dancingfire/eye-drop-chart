<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartGeneration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index()
    {
        // Get statistics per user
        $stats = User::select('users.id', 'users.name', 'users.email', 'users.company_name')
            ->leftJoin('chart_generations', 'users.id', '=', 'chart_generations.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.company_name')
            ->selectRaw('COUNT(chart_generations.id) as total_charts')
            ->selectRaw('MAX(chart_generations.created_at) as last_generated')
            ->orderByDesc('total_charts')
            ->get();

        // Get recent generations
        $recentGenerations = ChartGeneration::with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Get overall statistics
        $totalCharts = ChartGeneration::count();
        $totalUsers = User::whereHas('chartGenerations')->count();
        $chartsThisMonth = ChartGeneration::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.audit.index', compact('stats', 'recentGenerations', 'totalCharts', 'totalUsers', 'chartsThisMonth'));
    }
}
