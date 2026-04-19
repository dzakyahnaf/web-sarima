<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Fitur Selection - Analisis Lonjakan Kunjungan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-2">10 Minggu dengan Pengunjung Tertinggi</h3>
                    <p class="text-sm text-gray-500 mb-6">Analisis masa lalu yang menunjukkan terjadinya puncak lonjakan secara mendadak. Informasi ini berguna agar manajemen siap mengakomodasi peningkatan kunjungan saat pola sejenis terjadi di waktu yang akan datang.</p>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Peringkat</th>
                                    <th scope="col" class="px-6 py-3">Waktu (Tahun - Bulan - Minggu)</th>
                                    <th scope="col" class="px-6 py-3 text-center">Tiket Masuk (KTM)</th>
                                    <th scope="col" class="px-6 py-3 text-center">Glamping</th>
                                    <th scope="col" class="px-6 py-3 text-center">Total Kunjungan</th>
                                    <th scope="col" class="px-6 py-3 text-center">Status Lonjakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topWeeks as $index => $week)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-green-50 dark:hover:bg-gray-600 transition">
                                    <td class="px-6 py-4 font-bold">
                                        #{{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        Minggu ke-{{ $week['week'] }} | {{ $week['month'] }} {{ $week['year'] }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-green-600 font-bold">
                                        {{ number_format($week['ktm']) }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-amber-500 font-bold">
                                        {{ number_format($week['glamping']) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-lg text-purple-600">
                                        {{ number_format($week['total_visitors']) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($index < 3)
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded border border-red-400">Peak Demand</span>
                                        @elseif($index < 7)
                                            <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded border border-orange-400">High Traffic</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded border border-yellow-400">Spike</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Identical Conditions Hint -->
            <div class="bg-green-50 dark:bg-green-900/30 overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-bold text-lg text-green-800 dark:text-green-300 mb-2"><i class="fas fa-lightbulb"></i> Insight Pengunjung Bersamaan</h3>
                    <p class="text-sm">Berdasarkan data mining historis yang ada, kelompok pengunjung (wisatawan keluarga besar/rombongan) kerap hadir pada **minggu pertama dan minggu terakhir** di bulan liburan (Juni, Juli, Desember). Fokuskan penambahan staf tiket pada periode dengan label "Peak Demand".</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
