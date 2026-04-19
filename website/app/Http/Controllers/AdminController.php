<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\SarimaApiService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    protected $sarimaService;

    public function __construct(SarimaApiService $sarimaService)
    {
        $this->sarimaService = $sarimaService;
    }

    public function dashboard()
    {
        $stats = $this->sarimaService->getStats();
        $modelInfo = $this->sarimaService->getModelInfo();
        $historical = $this->sarimaService->getHistoricalMonthly();
        $usersCount = User::count();
        
        $data = [
            'total_visitors' => $stats['data']['total_visitors'] ?? 0,
            'peak_month' => $stats['data']['max_month'] ?? '-',
            'metrics' => $modelInfo['data']['metrics'] ?? null,
            'users_count' => $usersCount,
            'status' => $stats['success'] ?? false,
            'historical' => $historical['data'] ?? []
        ];

        return view('admin.dashboard', compact('data'));
    }

    public function dataInput()
    {
        $csvPath = base_path('../ml-sarima/data/raw/Drafting Data Bunihayu Rev.csv');
        $rowsCount = 0;
        $lastModified = '-';
        
        if (File::exists($csvPath)) {
            $fileContent = file($csvPath);
            $rowsCount = count($fileContent) - 1; // Subtract header
            $lastModified = date("Y-m-d H:i:s", File::lastModified($csvPath));
        }

        $datasets = [
            [
                'id' => 1,
                'filename' => 'Drafting Data Bunihayu Rev.csv',
                'uploaded_at' => $lastModified,
                'status' => 'Active',
                'rows' => $rowsCount
            ]
        ];

        return view('admin.data-input', compact('datasets'));
    }

    public function modelSettings()
    {
        $modelInfo = $this->sarimaService->getModelInfo();
        return view('admin.model-settings', compact('modelInfo'));
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function retrainModel(Request $request)
    {
        $result = $this->sarimaService->retrain();
        
        if ($result['success']) {
            return redirect()->back()->with('success', 'Model SARIMA berhasil di-retrain! Akurasi terbaru: ' . ($result['metrics']['quality'] ?? 'N/A'));
        }

        return redirect()->back()->with('error', 'Gagal melakukan retrain: ' . ($result['error'] ?? 'Unknown error'));
    }

    public function uploadDataset(Request $request)
    {
        $request->validate([
            'dataset' => 'required|file|mimes:csv,txt'
        ]);

        $csvPath = base_path('../ml-sarima/data/raw/Drafting Data Bunihayu Rev.csv');
        
        try {
            $file = $request->file('dataset');
            $file->move(dirname($csvPath), basename($csvPath));
            
            return redirect()->back()->with('success', 'Dataset berhasil diupload! Silakan klik Retrain Model di menu Model Settings.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal upload dataset: ' . $e->getMessage());
        }
    }

    public function storeManualData(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric',
            'bulan' => 'required|string',
            'minggu' => 'required|numeric|between:1,5',
            'ktm' => 'required|numeric',
            'glamping' => 'required|numeric',
        ]);

        $total = $request->ktm + $request->glamping;
        $row = "{$request->tahun},{$request->bulan},{$request->minggu},{$request->ktm},{$request->glamping},{$total}";

        $csvPath = base_path('../ml-sarima/data/raw/Drafting Data Bunihayu Rev.csv');

        try {
            $content = File::get($csvPath);
            // Ensure we start on a new line
            if (strlen($content) > 0 && substr($content, -1) !== "\n") {
                $row = "\n" . $row;
            } else {
                $row = $row . "\n"; // If it already has newline, append and ensure next one has too
            }
            
            File::append($csvPath, $row);
            return redirect()->back()->with('success', 'Data manual berhasil ditambahkan! Silakan klik Retrain Model untuk memperbarui prediksi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambah data: ' . $e->getMessage());
        }
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
        ]);

        return redirect()->back()->with('success', 'User added successfully');
    }
}
