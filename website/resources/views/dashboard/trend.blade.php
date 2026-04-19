<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Trend Analysis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-2">Trend Kunjungan Mingguan</h3>
                    <p class="text-sm text-gray-500 mb-6">Analisis pergerakan pengunjung berdasarkan musim liburan, cuaca, dan faktor musiman lainnya.</p>
                    
                    <div class="relative" style="height: 400px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4">Traffic Level Distribution</h3>
                        <div class="relative" style="height: 300px;">
                            <canvas id="pizzaChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="font-bold mb-2">Insights</h3>
                        <ul class="list-disc pl-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <li>Trend menunjukkan bahwa KTM selalu lebih dominan dibandingkan Glamping secara stabil di setiap bulan.</li>
                            <li>Ada penurunan pengunjung saat memasuki bulan puasa (Ramadan) yang menyebabkan data hilang/rendah di bulan Maret.</li>
                            <li>Kehadiran libur nasional dan weekend mendongkrak data mingguan secara tajam.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var weeklyRaw = @json($historicalWeekly['data'] ?? []);
            
            // Transform data for line chart
            var weeklyLabels = weeklyRaw.map(w => 'W' + w.week + ' ' + w.month + ' ' + w.year);
            var totalVisitors = weeklyRaw.map(w => w.total_visitors);
            var ktmVisitors = weeklyRaw.map(w => w.ktm);
            var glampingVisitors = weeklyRaw.map(w => w.glamping);
            
            // Pie chart data
            var trafficLevels = {
                'Peak Demand': 0,
                'High Traffic': 0,
                'Medium Traffic': 0,
                'Low Traffic': 0
            };
            weeklyRaw.forEach(function(w) {
                if(w.traffic_level && trafficLevels[w.traffic_level] !== undefined) {
                    trafficLevels[w.traffic_level]++;
                }
            });

            // Line Chart
            var trendCtx = document.getElementById('trendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: weeklyLabels,
                    datasets: [
                        {
                            label: 'Total Pengunjung',
                            data: totalVisitors,
                            borderColor: '#3B82F6', // blue-500
                            tension: 0.3,
                            fill: false
                        },
                        {
                            label: 'KTM',
                            data: ktmVisitors,
                            borderColor: '#10B981', // green-500
                            tension: 0.3,
                            fill: false
                        },
                        {
                            label: 'Glamping',
                            data: glampingVisitors,
                            borderColor: '#F59E0B', // amber-500
                            tension: 0.3,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { tooltip: { mode: 'index', intersect: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Pie Chart
            var pizzaCtx = document.getElementById('pizzaChart').getContext('2d');
            new Chart(pizzaCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(trafficLevels),
                    datasets: [{
                        data: Object.values(trafficLevels),
                        backgroundColor: [
                            '#EF4444', // Red (Peak)
                            '#F59E0B', // Amber (High)
                            '#3B82F6', // Blue (Medium)
                            '#10B981'  // Green (Low)
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
</x-app-layout>
