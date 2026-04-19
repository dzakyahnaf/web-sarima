<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Laporan Kunjungan Historis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(isset($historical['success']) && !$historical['success'])
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm flex items-center justify-between" role="alert">
                <div>
                    <p class="font-bold">⚠️ Layanan ML Offline</p>
                    <p class="text-sm">Laporan tidak dapat dimuat karena sistem tidak dapat terhubung ke Server Python.</p>
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Data Agregasi Bulanan ({{ $year == 'All' ? 'Semua Tahun' : $year }})</h3>
                    <div class="flex gap-2">
                        <button onclick="exportTableToCSV('laporan_kunjungan.csv')" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export CSV
                        </button>
                        <button onclick="window.print()" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-bold py-2 px-4 rounded inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print Laporan
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto p-6">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Bulan</th>
                                <th scope="col" class="px-6 py-3">Tahun</th>
                                <th scope="col" class="px-6 py-3">Pengunjung KTM</th>
                                <th scope="col" class="px-6 py-3">Pengunjung Glamping</th>
                                <th scope="col" class="px-6 py-3">Total Kunjungan</th>
                                <th scope="col" class="px-6 py-3">Status Traffic</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($historical['data']))
                                @php
                                    $filteredData = array_filter($historical['data'], function($item) use ($year) {
                                        return $item['year'] == $year || $year == 'All';
                                    });
                                @endphp
                                @foreach($filteredData as $row)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 font-medium">{{ $row['month'] }}</td>
                                    <td class="px-6 py-4">{{ $row['year'] }}</td>
                                    <td class="px-6 py-4">{{ number_format($row['ktm']) }}</td>
                                    <td class="px-6 py-4">{{ number_format($row['glamping']) }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ number_format($row['total_visitors']) }}</td>
                                    <td class="px-6 py-4">
                                        @if($row['traffic_level'] == 'Peak Demand')
                                            <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $row['traffic_level'] }}</span>
                                        @elseif($row['traffic_level'] == 'High Traffic')
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $row['traffic_level'] }}</span>
                                        @else
                                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $row['traffic_level'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center">Data Histori Belum Tersedia</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    function downloadCSV(csv, filename) {
        var csvFile;
        var downloadLink;
        csvFile = new Blob([csv], {type: "text/csv"});
        downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }

    function exportTableToCSV(filename) {
        var csv = [];
        var rows = document.querySelectorAll("table tr");
        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll("td, th");
            for (var j = 0; j < cols.length; j++) 
                row.push('"' + cols[j].innerText.trim() + '"');
            csv.push(row.join(","));        
        }
        downloadCSV(csv.join("\n"), filename);
    }
</script>
