<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SarimaApiService
{
    /**
     * The base URL of the Python Flask API
     */
    protected $baseUrl;

    public function __construct()
    {
        // Secara default menggunakan localhost:5000 jika belum di-set di .env
        $this->baseUrl = env('SARIMA_API_URL', 'http://localhost:5000');
    }

    /**
     * Mendapatkan data prediksi (default 6 bulan ke depan)
     */
    public function getPrediction($months = 12)
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/predict", [
                'months' => $months
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('SARIMA API Error: /api/predict', ['status' => $response->status(), 'response' => $response->body()]);
            return ['success' => false, 'error' => 'Gagal mengambil data prediksi dari API.'];
        } catch (\Exception $e) {
            Log::error('SARIMA API Exception: /api/predict', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => 'API SARIMA tidak dapat dihubungi. Pastikan server Python berjalan.'];
        }
    }

    /**
     * Mendapatkan data historis bulanan
     */
    public function getHistoricalMonthly()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/historical");

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('SARIMA API Error: /api/historical', ['status' => $response->status(), 'response' => $response->body()]);
            return ['success' => false, 'error' => 'Gagal mengambil data historis dari API.'];
        } catch (\Exception $e) {
            Log::error('SARIMA API Exception: /api/historical', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => 'API SARIMA tidak dapat dihubungi.'];
        }
    }

    /**
     * Mendapatkan data historis mingguan
     */
    public function getHistoricalWeekly()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/weekly");

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('SARIMA API Error: /api/weekly', ['status' => $response->status(), 'response' => $response->body()]);
            return ['success' => false, 'error' => 'Gagal mengambil data mingguan dari API.'];
        } catch (\Exception $e) {
            Log::error('SARIMA API Exception: /api/weekly', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => 'API SARIMA tidak dapat dihubungi.'];
        }
    }

    /**
     * Mendapatkan statistik umum
     */
    public function getStats()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/stats");

            if ($response->successful()) {
                return $response->json();
            }
            
            return ['success' => false, 'error' => 'Gagal mengambil data statistik.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'API SARIMA tidak dapat dihubungi.'];
        }
    }

    /**
     * Mendapatkan info model (akurasi dll)
     */
    public function getModelInfo()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/model-info");

            if ($response->successful()) {
                return $response->json();
            }
            
            return ['success' => false, 'error' => 'Gagal mengambil info model.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'API SARIMA tidak dapat dihubungi.'];
        }
    }

    /**
     * Trigger retraining of the SARIMA model via Python API
     */
    public function retrain()
    {
        try {
            $response = Http::timeout(60)->post("{$this->baseUrl}/api/retrain");

            if ($response->successful()) {
                return $response->json();
            }
            
            return ['success' => false, 'error' => 'Gagal melakukan retrain model.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'API SARIMA tidak dapat dihubungi untuk retraining.'];
        }
    }
}
