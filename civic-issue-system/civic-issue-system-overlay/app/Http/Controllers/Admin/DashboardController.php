<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    /** JSON feed for the dashboard charts (fetched via AJAX). */
    public function analytics(AnalyticsService $analytics)
    {
        return response()->json($analytics->all());
    }
}
