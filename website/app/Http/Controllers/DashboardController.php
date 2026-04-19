<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SarimaApiService;

class DashboardController extends Controller
{
    protected $sarimaService;

    public function __construct(SarimaApiService $sarimaService)
    {
        $this->sarimaService = $sarimaService;
    }

    /**
     * Display the overview dashboard.
     */
    public function overview()
    {
        $stats = $this->sarimaService->getStats();
        $historicalWeekly = $this->sarimaService->getHistoricalWeekly();
        
        // Use weekly data if available, fallback to empty array
        $weeklyData = isset($historicalWeekly['data']) ? $historicalWeekly['data'] : [];

        // Calculate simple trend metrics if available
        $data = [
            'total_visitors' => $stats['data']['total_visitors'] ?? 0,
            'avg_monthly' => $stats['data']['avg_monthly_visitors'] ?? 0,
            'peak_month' => $stats['data']['max_month'] ?? '-',
            'status' => $stats['success'] ?? false,
        ];

        return view('dashboard.overview', compact('data', 'weeklyData'));
    }

    /**
     * Display the advanced prediction dashboard with SARIMA metrics.
     */
    public function prediksi()
    {
        $historical = $this->sarimaService->getHistoricalMonthly();
        $prediction = $this->sarimaService->getPrediction(12);
        $modelInfo = $this->sarimaService->getModelInfo();

        return view('dashboard.prediksi', compact('historical', 'prediction', 'modelInfo'));
    }

    /**
     * Display interactive trend analysis.
     */
    public function trend()
    {
        $historicalWeekly = $this->sarimaService->getHistoricalWeekly();
        
        return view('dashboard.trend', compact('historicalWeekly'));
    }

    /**
     * Display selection feature to find peak visitors/patterns.
     */
    public function selection()
    {
        $historicalWeekly = $this->sarimaService->getHistoricalWeekly();
        
        // Filter out peak visitors logic directly in view or controller
        $weeklyData = isset($historicalWeekly['data']) ? $historicalWeekly['data'] : [];
        
        // Sort by highest total_visitors to find peak weeks
        usort($weeklyData, function($a, $b) {
            return $b['total_visitors'] <=> $a['total_visitors'];
        });

        // Get top 10 weeks
        $topWeeks = array_slice($weeklyData, 0, 10);

        return view('dashboard.selection', compact('topWeeks', 'weeklyData'));
    }

    /**
     * Display reports section.
     */
    public function report(Request $request)
    {
        $historical = $this->sarimaService->getHistoricalMonthly();
        
        // Find max year in historical data for default view
        $defaultYear = 2025; // Hard fallback
        if (isset($historical['data']) && !empty($historical['data'])) {
            $years = array_column($historical['data'], 'year');
            $defaultYear = max($years);
        }

        $year = $request->query('year', $defaultYear);
        
        return view('dashboard.report', compact('historical', 'year'));
    }
}
