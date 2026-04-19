<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Overview') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(isset($data['status']) && $data['status'] == false)
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Error API</p>
                <p>Tidak dapat terhubung ke server SARIMA Flask API. Pastikan service Python berjalan.</p>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Card 1 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-b-4 border-green-500">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Kunjungan (Periode Data)</div>
                        <div class="text-3xl font-bold text-green-600">{{ number_format($data['total_visitors']) }}</div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-b-4 border-blue-500">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Bulanan</div>
                        <div class="text-3xl font-bold text-blue-600">{{ number_format($data['avg_monthly'], 1) }}</div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-b-4 border-orange-500">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Bulan Puncak Historis</div>
                        <div class="text-2xl font-bold text-orange-500">{{ $data['peak_month'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Insight Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4 border-b pb-2">Distribusi KTM vs Glamping (Historis)</h3>
                        <div class="relative" style="height: 300px; width: 100%;">
                            <canvas id="ktmGlampingChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4 border-b pb-2">Proporsi Kunjungan KTM & Glamping</h3>
                        <div class="relative flex justify-center items-center" style="height: 300px; width: 100%;">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-lg font-bold">Trend Kunjungan per Minggu</h3>
                        <span class="text-sm text-gray-500">Minggu 1 - 5 per Bulan</span>
                    </div>
                    <div class="relative" style="height: 350px; width: 100%;">
                        <canvas id="weeklyTrendChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart Config -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const weeklyData = @json($weeklyData);
            
            // Process data for charts
            let labels = [];
            let ktmData = [];
            let glampingData = [];
            let totalKTM = 0;
            let totalGlamping = 0;
            
            // We only take the last 20 weeks for the bar chart so it's not too cluttered
            const recentWeeks = weeklyData.slice(-20);
            
            recentWeeks.forEach(d => {
                labels.push(`${d.month} W${d.week} '${d.year.toString().substr(-2)}`);
                ktmData.push(d.ktm);
                glampingData.push(d.glamping);
            });
            
            // Aggregate totals for the pie chart
            weeklyData.forEach(d => {
                totalKTM += d.ktm;
                totalGlamping += d.glamping;
            });

            // 1. Stacked Bar Chart: KTM vs Glamping Recent Weeks
            new Chart(document.getElementById('ktmGlampingChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'KTM',
                            data: ktmData,
                            backgroundColor: '#10B981', // green
                        },
                        {
                            label: 'Glamping',
                            data: glampingData,
                            backgroundColor: '#F59E0B', // amber
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true }
                    }
                }
            });
            
            // 2. Pie Chart
            new Chart(document.getElementById('pieChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['KTM Data', 'Glamping Data'],
                    datasets: [{
                        data: [totalKTM, totalGlamping],
                        backgroundColor: ['#10B981', '#F59E0B'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
            
            // 3. Weekly Overview (Line Chart)
            const allWeeklyLabels = weeklyData.map(d => `${d.month.substring(0,3)} W${d.week}`);
            const allWeeklyTotal = weeklyData.map(d => d.total_visitors);
            
            new Chart(document.getElementById('weeklyTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: allWeeklyLabels,
                    datasets: [{
                        label: 'Total Kunjungan per Minggu',
                        data: allWeeklyTotal,
                        borderColor: '#3B82F6', // Blue
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
</x-app-layout>
