# Dokumentasi Sistem Prediksi Wisata Bunihayu (SARIMA)

> [!IMPORTANT]
> ### ✅ CHECKLIST SEBELUM DEMO / PENGETESAN
> Agar dashboard tidak kosong, pastikan kedua server di bawah ini sudah menyala:
> 1. **Server Web (Laravel)**: Buka terminal di folder `website`, jalankan `php artisan serve`.
> 2. **Server ML (Python)**: Buka terminal di folder `ml-sarima`, aktifkan venv, jalankan `python src/api.py`.
> 
> *Jika server ML mati, dashboard akan menampilkan peringatan "Layanan ML Offline".*

---

## 🏗️ Arsitektur Sistem
Sistem ini dikembangkan untuk memberikan solusi prediktif bagi manajemen Wana Wisata Kampoeng Ciherang Bunihayu dalam memantau dan memperkirakan lonjakan jumlah wisatawan.

---

## 🎯 Tujuan Aplikasi
Tujuan utama aplikasi ini adalah untuk **mentransformasi data kunjungan historis yang kompleks dan tersebar menjadi informasi strategis** yang mudah dipahami melalui dashboard visual. Dengan adanya prediksi SARIMA, pihak pengelola dapat:
- Menyiapkan jumlah staf yang sesuai berdasarkan perkiraan lonjakan.
- Mengatur stok logistik dan fasilitas glamping.
- Menentukan strategi promosi pada bulan-bulan yang diprediksi akan mengalami penurunan (*Low Traffic*).

---

## 🖥️ Perbedaan Dashboard Admin vs User

| Fitur | Dashboard User (Pengunjung) | Dashboard Admin (Staff) |
| :--- | :--- | :--- |
| **Akses** | Terbatas (Informasi Saja) | Penuh (Manajemen & Konfigurasi) |
| **Visualisasi** | Grafik Trend, Prediksi, Laporan | Grafik Statis & Metrik Akurasi Model |
| **Kontrol Data** | Lihat & Export (Read-only) | Input CSV, Edit Data, Hapus Data |
| **Kontrol ML** | Tidak Ada | **Trigger Retrain Model** |
| **Manajemen User** | Tidak Ada | Kelola Hak Akses Pengguna |

---

## 🏗️ Tech Stack (Teknologi yang Digunakan)
Sistem ini merupakan **Integrasi Web + Machine Learning End-to-End** yang berjalan secara terpisah namun saling berkomunikasi via API.

- **Web Frontend & Backend (Laravel)**:
  - Framework: Laravel 11 (PHP 8.2+)
  - Styling: Tailwind CSS & Vanilla CSS (Pine Green Theme)
  - Visualisasi: Chart.js (User) & ApexCharts (Admin)
- **Machine Learning Microservice (Python)**:
  - Language: Python 3.10+
  - Web Server: Flask (REST API)
  - Data Processing: Pandas, Numpy
  - Time Series: Statsmodels (SARIMA), Pmdarima (Auto-Optimization)

---

## 💾 Tech Stack Database
Untuk proyek ini, kita menggunakan **SQLite** sebagai sistem manajemen database.
- **Kenapa SQLite?**: SQLite tidak memerlukan instalasi server database mandiri (serverless). Database disimpan dalam satu file fisik (`database/database.sqlite`), sehingga sangat portable untuk dipindahkan antar komputer klien tanpa perlu setup MySQL atau PostgreSQL yang rumit.
- **Transparansi Data**: Sangat cocok untuk kebutuhan project joki/tugas akhir karena mudah diintegrasikan dan proses migrasi data (`php artisan migrate`) sangat stabil.

---

## ⚙️ Konfigurasi .env (Penting)
File `.env` digunakan untuk mengatur variabel lingkungan. Pastikan poin berikut terisi:

1. **Database**: Menggunakan driver `sqlite`.
   ```env
   DB_CONNECTION=sqlite
   # DB_DATABASE tidak perlu diisi jika menggunakan default Laravel 11
   ```
2. **Key API SARIMA**: Laravel perlu mengetahui alamat server Python Flask agar grafik prediksi tampil.
   ```env
   SARIMA_API_URL=http://localhost:5000
   ```
3. **App Key**: Pastikan sudah melakukan `php artisan key:generate`.

---

## 📊 Tahapan Pengolahan Data (Preprocessing)
Data mentah dari `@Drafting Data Bunihayu Rev.csv` memiliki tantangan tersendiri (sel kosong, teks non-numerik, dan ketidakteraturan mingguan). Berikut langkah-langkah yang dilakukan:

1. **Data Cleaning**:
   - **Forward-fill**: Melakukan pengisian baris kosong pada kolom Tahun dan Bulan (akibat *merged cells* di Excel).
   - **Text Handling**: Menghapus teks non-numerik seperti "RAMADAN" dan mengubahnya menjadi nilai numerik yang valid atau kosong.
   - **Missing Value Handling**: Pada bulan Ramadan (Maret) di mana data kosong, sistem melakukan identifikasi awal sebelum tahap interpolasi.
2. **Transformasi Data**:
   - Mengubah format penanggalan dari teks ("Januari") menjadi angka (1).
   - Membuat objek **DateTime** presisi dengan menggabungkan Tahun + Bulan + Minggu ke-N.
3. **Mapping Data**:
   - Menambahkan pelabelan otomatis berdasarkan volume pengunjung:
     - < 150/bulan: *Low Traffic*
     - 150 - 300: *Medium Traffic*
     - 301 - 500: *High Traffic*
     - \> 500: *Peak Demand*
4. **Agregasi Data**:
   - Karena SARIMA bekerja paling baik pada pola musiman (*Seasonality*), data mingguan (yang terlalu fluktuatif) **diagregasi menjadi data bulanan**.
   - Melakukan **Linear Interpolation** untuk mengisi kekosongan data pada bulan Ramadan agar deret waktu (*time series*) menjadi kontinu.

---

## 🧠 Tahapan Process Machine Learning (SARIMA)
Setelah data bersih, pipeline Machine Learning menjalankan proses berikut secara utuh:

1. **Stationarity Test (ADF Test)**: Mengecek stabilitas data. Jika p-value > 0.05, sistem melakukan *Differencing* (d) secara otomatis agar data menjadi stasioner.
2. **Auto-SARIMA Tuning**: Menggunakan grid search untuk mencari parameter terbaik `(p, d, q)` untuk tren non-musiman dan `(P, D, Q, s)` untuk tren musiman (dengan `s=12` untuk siklus tahunan).
3. **Model Training**: Melakukan fitting model SARIMA pada 80% data pertama untuk proses pembelajaran.
4. **Evaluasi Metrik**: Menghitung skor error:
   - **MAE**: Rata-rata selisih antara pengunjung asli vs prediksi.
   - **RMSE**: Penalti untuk error yang besar.
   - **MAPE**: Persentase akurasi relatif.
5. **Forecasting (Prediksi)**: Melakukan prediksi untuk 12 bulan ke depan sekaligus menghitung **Confidence Interval 95%** (batas atas/optimis dan batas bawah/pesimis).
6. **Integration**: Hasil akhir di-*export* ke format JSON yang siap dikonsumsi oleh Laravel secara *real-time*.

## 🚀 Panduan Menjalankan Project

Untuk menjalankan sistem ini secara lokal, ikuti langkah-langkah di bawah ini. Pastikan Anda memiliki **PHP 8.2+**, **Composer**, **Node.js**, dan **Python 3.10+** terinstal.

### 1. Menjalankan Machine Learning API (Python Flask)
Microservice ini harus berjalan agar website dapat menampilkan grafik dan prediksi.
```bash
# Masuk ke direktori ML
cd ml-sarima

# (Opsional) Buat virtual environment
python -m venv venv
venv\Scripts\activate

# Instal library yang dibutuhkan
pip install -r requirements.txt

# Jalankan server API
python src/api.py
```

### 2. Konfigurasi dan Jalankan Website (Laravel)
Buka terminal baru untuk menjalankan website.
```bash
# Masuk ke direktori website
cd website

# Instal dependensi PHP (Composer) dan Javascript (NPM)
composer install
npm install
npm run build

# Salin file environment dan generate key
copy .env.example .env
php artisan key:generate

# Konfigurasi Database, Migrasi, dan Seed Admin
# Secara default menggunakan SQLite (database/database.sqlite)
php artisan migrate --seed

# Jalankan server Laravel
php artisan serve
```

### 3. Akun Akses Default
Setelah menjalankan seeder, gunakan akun berikut untuk masuk ke dashboard:
- **Email**: `admin@bunihayu.com`
- **Password**: `password`

---

## 🏗️ Integrasi Database
Project ini mendemonstrasikan integrasi data secara penuh:
1. **SQLite/PostgreSQL**: Menyimpan data user, logs, dan metadata dataset.
2. **Flat-file CSV**: Dataset utama disimpan di `ml-sarima/data/raw/` untuk diproses oleh engine Python.
3. **JSON API**: Hasil prediksi SARIMA didistribusikan dari Python ke Laravel dalam format JSON untuk performa rendering yang cepat.

---

## 🛠️ Manajemen Dataset & Input Data (Baru)

Sistem kini dilengkapi dengan fitur manajemen data langsung dari Dashboard Admin tanpa perlu menyentuh file CSV secara manual:

### 1. Upload Dataset
- Digunakan untuk mengganti seluruh data historis sekaligus (bulk update).
- File yang di-upload harus berupa CSV dengan kolom: `Tahun, Bulan, Minggu, KTM, Glamping`.
- Sistem akan otomatis memvalidasi dan mengganti file RAW data di backend ML.

### 2. Input Data Manual
- Digunakan untuk menambah data pengunjung terbaru baris demi baris.
- Cukup masukkan Tahun, Bulan, dan Jumlah Pengunjung (KTM/Glamping).
- Sistem akan menghitung total secara otomatis dan melakukan sinkronisasi ke file CSV.

### 3. Alur Sinkronisasi
Setelah melakukan Upload atau Input Manual, **WAJIB** melakukan langkah berikut agar perubahan muncul di grafik:
1. Pergi ke menu **Model Settings**.
2. Klik tombol **"Jalankan Retrain Model"**.
3. Tunggu hingga muncul notifikasi sukses (Python sedang melatih ulang model dengan data terbaru).

---

## ⚠️ Catatan Penting & Troubleshooting

### 1. Masalah Instalasi pada Python 3.13+
Jika Anda menggunakan **Python 3.13** (atau versi lebih baru) dan mengalami error `subprocess-exited-with-error` saat melakukan `pip install`, pastikan langkah berikut telah dilakukan:
- **Upgrade pip**: Jalankan `python -m pip install --upgrade pip` terlebih dahulu.
- **Versi Library**: Gunakan versi library terbaru di `requirements.txt` (misal: `numpy>=2.1.0` dan `scikit-learn>=1.5.2`) agar `pip` mengunduh file biner (`.whl`) dan tidak melakukan kompilasi dari source code.

### 2. Koneksi Laravel ke API Python (SARIMA_API_URL)
Sistem ini sangat bergantung pada komunikasi antar-service. 
- Jika grafik prediksi tidak muncul/kosong, periksa apakah file `.env` sudah memiliki `SARIMA_API_URL=http://localhost:5000`.
- Pastikan server Python (`src/api.py`) sudah berjalan sebelum membuka dashboard website.

### 3. Permission pada Windows
Jika perintah `venv\Scripts\activate` gagal karena masalah kebijakan (*Execution Policy*), jalankan perintah ini di PowerShell dengan hak akses Administrator:
`Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser`

---
*Dokumentasi ini diperbarui untuk membantu proses deployment dan eksekusi sistem bagi pengembang.*
