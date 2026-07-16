<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function __invoke(): View
    {
        return view("admin.dashboard", [
            "chartData" => $this->dashboardService->getChartData(),
            "mapData" => $this->dashboardService->getMapData(),
            "laporanStats" => $this->dashboardService->getLaporanStats(),
        ]);
    }
}
