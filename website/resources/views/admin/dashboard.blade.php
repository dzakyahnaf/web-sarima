@extends('admin.layouts.admin')

@section('header', 'Dashboard Overview')

@section('content')
<div class="space-y-6">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Pengunjung</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($data['total_visitors']) }}</h3>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/40 flex items-center justify-center text-green-600 dark:text-green-400">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Bulan Puncak</p>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ $data['peak_month'] }}</h3>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center text-purple-600 dark:text-purple-400">
                <i class="fas fa-user-shield text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Jumlah User</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $data['users_count'] }}</h3>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center text-orange-600 dark:text-orange-400">
                <i class="fas fa-bolt text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Model Status</p>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Active (SARIMA)</h3>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Trend Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Trend Kunjungan Bulanan</h3>
                <span class="text-xs font-medium text-gray-400">Historical Data Visualization</span>
            </div>
            <div id="historicalChart" class="h-80 w-full"></div>
        </div>

        <!-- Model Metrics Radar/Bar Chart -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Model Performance</h3>
                <span class="text-xs font-medium text-gray-400 text-green-500">MAE/RMSE/MAPE</span>
            </div>
            <div id="metricsChart" class="h-80 w-full"></div>
        </div>
    </div>

    <!-- Recent Data Table Placeholder -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Ringkasan Data Terbaru</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-400 font-semibold text-center">
                    <tr>
                        <th class="px-6 py-3 text-left">Bulan</th>
                        <th class="px-6 py-3">Tahun</th>
                        <th class="px-6 py-3">KTM</th>
                        <th class="px-6 py-3">Glamping</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Growth</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse(array_slice($data['historical'], -5) as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 text-gray-900 dark:text-gray-200 font-medium">{{ $row['month'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $row['year'] }}</td>
                        <td class="px-6 py-4 text-center">{{ number_format($row['ktm']) }}</td>
                        <td class="px-6 py-4 text-center">{{ number_format($row['glamping']) }}</td>
                        <td class="px-6 py-4 text-center font-bold text-green-600 dark:text-green-400">{{ number_format($row['total_visitors']) }}</td>
                        <td class="px-6 py-4 text-center">
                            @if(isset($row['growth']))
                                <span class="{{ $row['growth'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $row['growth'] }}%
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data tersedia</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const historicalData = @json($data['historical']);
        
        // 1. Historical Trend Chart
        const trendOptions = {
            series: [{
                name: 'KTM',
                data: historicalData.map(item => item.ktm)
            }, {
                name: 'Glamping',
                data: historicalData.map(item => item.glamping)
            }],
            chart: {
                height: 320,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#10B981', '#F59E0B'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: historicalData.map(item => `${item.month} ${item.year}`),
                labels: { rotate: -45, style: { fontSize: '10px' } }
            },
            tooltip: { theme: 'dark' },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            }
        };
        const historicalChart = new ApexCharts(document.querySelector("#historicalChart"), trendOptions);
        historicalChart.render();

        // 2. Metrics Chart
        const metrics = @json($data['metrics']);
        const metricsOptions = {
            series: [{
                data: [
                    metrics?.mae || 0,
                    metrics?.rmse || 0,
                    metrics?.mape || 0
                ]
            }],
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false }
            },
            colors: ['#8B5CF6'],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '50%',
                    distributed: true
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: ['MAE', 'RMSE', 'MAPE (%)'],
                labels: { style: { colors: '#94a3b8' } }
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4
            }
        };
        const metricsChart = new ApexCharts(document.querySelector("#metricsChart"), metricsOptions);
        metricsChart.render();
    });
</script>
@endsection
