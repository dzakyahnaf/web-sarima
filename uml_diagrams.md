# Dokumentasi UML - Sistem Prediksi Kunjungan Wisata Bunihayu

Dokumen ini berisi pemodelan sistem menggunakan bahasa Mermaid untuk Use Case Diagram dan Activity Diagram, sesuai dengan requirement proyek.

## 1. Use Case Diagram

Diagram Use Case menggambarkan interaksi antara aktor (User dan Admin) dengan sistem aplikasi web Laravel.

```mermaid
flowchart LR
    %% Actors
    User(["Pengunjung (User)"])
    Admin(["Administrator"])
    
    %% System Boundary
    subgraph "Sistem Prediksi Wisata Bunihayu"
        UC1("Akses Landing Page")
        UC2("Registrasi (Sign Up)")
        UC3("Login")
        UC4("Lihat Dashboard Overview")
        UC5("Lihat Prediksi SARIMA")
        UC6("Lihat Analisis Trend")
        UC7("Cetak Laporan Kunjungan")
        UC8("Kelola Data Kunjungan CSV")
        UC9("Jalankan Ulang Model (Retrain)")
        UC10("Kelola Parameter Sistem")
    end
    
    %% Relationships
    User --> UC1
    User --> UC2
    User --> UC3
    User --> UC4
    User --> UC5
    User --> UC6
    User --> UC7
    
    Admin --> UC3
    Admin --> UC4
    Admin --> UC5
    Admin --> UC6
    Admin --> UC7
    Admin --> UC8
    Admin --> UC9
    Admin --> UC10
```

## 2. Activity Diagram (Alur Proses Prediksi SARIMA)

Activity Diagram di bawah ini menggambarkan alur dari saat data historis dimasukkan, diproses oleh Machine Learning, hingga divisualisasikan oleh Website.

```mermaid
flowchart TD
    Start((Mulai)) --> A[Admin login ke sistem]
    A --> B[Admin memasukkan/update Data Kunjungan]
    B --> C{Picu proses Retrain?}
    
    C -- Ya --> D[Flask API: Menjalankan Preprocessing]
    D --> E[Flask API: Data Cleaning & Transformasi]
    E --> F[Flask API: Agregasi Mingguan ke Bulanan]
    F --> G[Flask API: Stationarity Test (ADF)]
    G --> H[Flask API: Parameter Selection (Auto ARIMA)]
    H --> I[Flask API: Training SARIMA Model]
    I --> J[Flask API: Evaluasi Metrik & Generate Forecast]
    J --> K[Flask API: Export JSON/Model]
    
    C -- Tidak --> L[User login ke Dashboard]
    K --> L
    
    L --> M[Laravel mengirim GET Request ke Flask API]
    M --> N[Flask API merespon dengan Time-Series JSON]
    N --> O[Laravel merender Dashboard View]
    O --> P[Chart.js memvisualisasikan Grafik Trend Historis vs Prediksi]
    P --> Q[User memahami estimasi lonjakan/penurunan traffic]
    Q --> End((Selesai))
```

## 3. Flowchart Preprocessing Python

Berikut adalah alur logika khusus di sisi Machine Learning Pipeline.

```mermaid
flowchart TD
    A([Input Raw CSV]) --> B[Tahap 1: Data Cleaning]
    B --> C(Fill-forward Tahun & Bulan)
    C --> D(Hapus/replace string kosong)
    D --> E[Tahap 2: Transformasi Data]
    E --> F(Mapping Nama Bulan jadi Nomor)
    F --> G(Pembuatan Kolom 'Date')
    G --> H[Tahap 3: Mapping Traffic]
    H --> I(Labeling Low/Medium/High/Peak Traffic)
    I --> J[Tahap 4: Agregasi]
    J --> K(Resample dari Mingguan ke Bulanan)
    K --> L{Bulan Kosong?}
    L -- Ya --> M[Linear Interpolation]
    L -- Tidak --> N
    M --> N([Output Data Agregasi Bulanan Terbersih])
```
