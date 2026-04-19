@extends('admin.layouts.admin')

@section('header', 'Model Configuration & Settings')

@section('content')
<div class="space-y-6">
    <!-- Model Info Card -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex flex-col lg:flex-row gap-8 justify-between">
            <div class="space-y-4 max-w-2xl">
                <div class="flex items-center gap-2 text-green-600 dark:text-green-400 font-bold uppercase tracking-widest text-xs">
                    <i class="fas fa-brain"></i>
                    <span>Trained Model Information</span>
                </div>
                <h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">SARIMA Prediction Engine</h3>
                <p class="text-gray-500 dark:text-gray-400">
                    Sistem menggunakan algoritma <span class="font-bold text-gray-800 dark:text-gray-200">Seasonal Autoregressive Integrated Moving Average (SARIMA)</span>. Model ini dioptimalkan untuk menangani pola musiman bulanan pada data kunjungan wisata Bunihayu.
                </p>

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700">
                        <span class="block text-xs text-gray-400 uppercase font-bold mb-1">SARIMA Order</span>
                        <span class="text-lg font-mono font-bold text-gray-700 dark:text-gray-200">
                            ({{ isset($modelInfo['data']['sarima_order']) ? implode(',', $modelInfo['data']['sarima_order']) : '?, ?, ?' }})
                        </span>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700">
                        <span class="block text-xs text-gray-400 uppercase font-bold mb-1">Seasonal Order</span>
                        <span class="text-lg font-mono font-bold text-gray-700 dark:text-gray-200">
                            ({{ isset($modelInfo['data']['seasonal_order']) ? implode(',', $modelInfo['data']['seasonal_order']) : '?, ?, ?, ?' }})
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col justify-center items-center lg:items-end gap-6 min-w-[300px]">
                <div class="text-center lg:text-right">
                    <div class="text-xs text-gray-400 uppercase font-bold mb-1">Model Quality Score</div>
                    <div class="text-5xl font-black text-green-500">
                        {{ $modelInfo['data']['metrics']['quality'] ?? 'Stable' }}
                    </div>
                </div>
                
                <form action="{{ route('admin.modelSettings.retrain') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melakukan retrain model? Proses ini akan memakan waktu beberapa detik hingga menit.')">
                    @csrf
                    <button type="submit" class="group relative px-8 py-4 bg-slate-900 text-white font-bold rounded-xl overflow-hidden hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/20">
                        <div class="relative flex items-center gap-3">
                            <i class="fas fa-sync-alt group-hover:rotate-180 transition-transform duration-700"></i>
                            <span>Jalankan Retrain Model</span>
                        </div>
                    </button>
                </form>
                <p class="text-[10px] text-gray-400 max-w-[200px] text-center lg:text-right">
                    *Gunakan fitur ini setelah melakukan upload dataset baru di menu Data Input.
                </p>
            </div>
        </div>
    </div>

    <!-- Metrics Deep Dive -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-blue-500 font-bold mb-2">MAE</div>
            <div class="text-3xl font-bold">{{ $modelInfo['data']['metrics']['mae'] ?? '0' }}</div>
            <p class="text-xs text-gray-400 mt-2">Mean Absolute Error (Rata-rata error absolut kunjungan)</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-purple-500 font-bold mb-2">RMSE</div>
            <div class="text-3xl font-bold">{{ $modelInfo['data']['metrics']['rmse'] ?? '0' }}</div>
            <p class="text-xs text-gray-400 mt-2">Root Mean Square Error (Magnitude error kuadratik)</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-orange-500 font-bold mb-2">MAPE</div>
            <div class="text-3xl font-bold">{{ $modelInfo['data']['metrics']['mape'] ?? '0' }}%</div>
            <p class="text-xs text-gray-400 mt-2">Mean Absolute Percentage Error (Persentase error rata-rata)</p>
        </div>
    </div>
</div>
@endsection
