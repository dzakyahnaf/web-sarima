<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Prediksi Rinci (Model SARIMA)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Model Metrics Area -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-primary">Informasi Model SARIMA</h3>
                        <p class="text-sm text-gray-500 w-full mb-4">
                        Order SARIMA: <span class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">({{ isset($modelInfo['data']['sarima_order']) ? implode(',', $modelInfo['data']['sarima_order']) : '?,?' }}) x ({{ isset($modelInfo['data']['seasonal_order']) ? implode(',', $modelInfo['data']['seasonal_order']) : '?,?' }})</span>
                        </p>
                    </div>
                    <div class="flex gap-4">
                        <div class="text-center px-4 py-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                            <div class="text-xs text-blue-600 dark:text-blue-400 font-bold uppercase">MAPE</div>
                            <div class="text-xl font-bold">{{ isset($modelInfo['data']['metrics']['mape']) ? $modelInfo['data']['metrics']['mape'] . '%' : '-' }}</div>
                        </div>
                        <div class="text-center px-4 py-2 bg-green-50 dark:bg-green-900/30 rounded-lg">
                            <div class="text-xs text-green-600 dark:text-green-400 font-bold uppercase">MAE</div>
                            <div class="text-xl font-bold">{{ isset($modelInfo['data']['metrics']['mae']) ? $modelInfo['data']['metrics']['mae'] : '-' }}</div>
                        </div>
                        <div class="text-center px-4 py-2 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                            <div class="text-xs text-purple-600 dark:text-purple-400 font-bold uppercase">RMSE</div>
                            <div class="text-xl font-bold">{{ isset($modelInfo['data']['metrics']['rmse']) ? $modelInfo['data']['metrics']['rmse'] : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical vs Forecast Graph -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Grafik Data Historis & Prediksi Masa Depan</h3>
                    <div class="relative" style="height: 400px; width: 100%;">
                        <canvas id="fullChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Tabel Hasil Prediksi 12 Bulan</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Bulan</th>
                                    <th scope="col" class="px-6 py-3">Tahun</th>
                                    <th scope="col" class="px-6 py-3">Prediksi Kunjungan (Nilai Tengah)</th>
                                    <th scope="col" class="px-6 py-3">Batas Bawah (Pessimistic)</th>
                                    <th scope="col" class="px-6 py-3">Batas Atas (Optimistic)</th>
                                    <th scope="col" class="px-6 py-3">Tingkat Kepercayaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($prediction['data']))
                                    @foreach($prediction['data'] as $pred)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                            {{ $pred['month'] }}
                                        </td>
                                        <td class="px-6 py-4">{{ $pred['year'] }}</td>
                                        <td class="px-6 py-4 font-bold text-purple-600">{{ number_format($pred['predicted_visitors']) }}</td>
                                        <td class="px-6 py-4 text-red-500">{{ number_format($pred['lower_ci']) }}</td>
                                        <td class="px-6 py-4 text-green-500">{{ number_format($pred['upper_ci']) }}</td>
                                        <td class="px-6 py-4">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-2">
                                              <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $pred['confidence_pct'] }}%"></div>
                                            </div>
                                            <span class="text-xs">{{ $pred['confidence_pct'] }}%</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center">Data Prediksi Belum Tersedia</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('fullChart').getContext('2d');
            
            var historicalRaw = @json(isset($historical['data']) ? $historical['data'] : []);
            var predictionRaw = @json(isset($prediction['data']) ? $prediction['data'] : []);
            
            var histLabels = historicalRaw.map(h => h.month + ' ' + h.year);
            var histData = historicalRaw.map(h => h.total_visitors);
            
            var predLabels = predictionRaw.map(p => p.month + ' ' + p.year);
            var predData = predictionRaw.map(p => p.predicted_visitors);
            var predLower = predictionRaw.map(p => p.lower_ci);
            var predUpper = predictionRaw.map(p => p.upper_ci);
            
            // Combine labels and data
            var allLabels = histLabels.concat(predLabels);
            
            // Pad prediction arrays with nulls so they align on X axis
            var paddedPredData = Array(histLabels.length).fill(null).concat(predData);
            
            // To make the line continuous, we put the last historical point into the prediction array
            if(histData.length > 0 && predData.length > 0) {
                var lastHistValue = histData[histData.length - 1];
                paddedPredData[histLabels.length - 1] = lastHistValue;
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: allLabels,
                    datasets: [
                        {
                            label: 'Data Historis (Aktual)',
                            data: histData,
                            borderColor: '#10B981', // green-500
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.2,
                            pointRadius: 3
                        },
                        {
                            label: 'Prediksi SARIMA',
                            data: paddedPredData,
                            borderColor: '#8B5CF6', // purple-500
                            backgroundColor: 'transparent',
                            borderWidth: 2.5,
                            borderDash: [5, 5],
                            tension: 0.2,
                            pointRadius: 4,
                            pointBackgroundColor: '#8B5CF6'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
</x-app-layout>
