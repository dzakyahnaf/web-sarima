@extends('admin.layouts.admin')

@section('header', 'Data Input & Management')

@section('content')
<div class="space-y-6">
    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Upload Section -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 h-full">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2 flex items-center gap-2">
                <i class="fas fa-file-csv text-blue-500"></i>
                Upload Dataset Baru
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Pilih file CSV untuk memperbarui seluruh data historis sekaligus.</p>
            
            <form action="{{ route('admin.dataInput.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center justify-center w-full">
                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-56 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400 font-semibold">Klik untuk memilih file</p>
                            <p class="text-xs text-gray-400">File .csv atau .txt saja</p>
                        </div>
                        <input id="dropzone-file" type="file" class="hidden" name="dataset" accept=".csv,.txt" required />
                    </label>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2">
                        <i class="fas fa-upload"></i>
                        <span>Upload Dataset</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Manual Input Section -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 h-full">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2 flex items-center gap-2">
                <i class="fas fa-keyboard text-green-500"></i>
                Input Data Manual
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Tambahkan satu baris data pengunjung terbaru secara cepat.</p>
            
            <form action="{{ route('admin.dataInput.manual') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tahun</label>
                        <input type="number" name="tahun" value="{{ date('Y') }}" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Bulan</label>
                        <select name="bulan" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-green-500 focus:border-green-500" required>
                            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Minggu Ke-</label>
                    <input type="number" name="minggu" min="1" max="5" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-green-500 focus:border-green-500" placeholder="1-5" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">KTM</label>
                        <input type="number" name="ktm" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-green-500 focus:border-green-500" placeholder="0" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Glamping</label>
                        <input type="number" name="glamping" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-green-500 focus:border-green-500" placeholder="0" required>
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-green-600/20 transition-all flex items-center gap-2 w-full justify-center">
                        <i class="fas fa-plus"></i>
                        <span>Tambahkan Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dataset List -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-list text-green-500"></i>
            Status Dataset Saat Ini
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-center">Tipe File</th>
                        <th class="px-6 py-4">Filename</th>
                        <th class="px-6 py-4">Terakhir Diperbarui</th>
                        <th class="px-6 py-4 text-center">Jumlah Baris</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($datasets as $dataset)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all">
                        <td class="px-6 py-4 text-center">
                            <i class="far fa-file-alt text-2xl text-blue-500"></i>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ $dataset['filename'] }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">
                            {{ $dataset['uploaded_at'] }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold">
                            {{ $dataset['rows'] }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                {{ $dataset['status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-lg flex items-start gap-3">
            <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
            <p class="text-xs text-amber-700 dark:text-amber-400">
                <strong>Catatan Integrasi:</strong> Setelah melakukan upload dataset atau input data manual, pastikan Anda pergi ke menu <a href="{{ route('admin.modelSettings') }}" class="underline font-bold">Model Settings</a> untuk menjalankan <strong>Retrain Model</strong> agar statistik dan prediksi terbaru muncul di dashboard.
            </p>
        </div>
    </div>
</div>
@endsection
