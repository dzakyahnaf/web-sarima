"""
Preprocessing Module — Sistem Prediksi Kunjungan Wisata Bunihayu
===============================================================
Tahapan preprocessing data kunjungan wisata:
1. Data Cleaning     — Handle missing values, forward-fill, remove invalid rows
2. Transformasi Data — Konversi ke datetime index, time series formatting
3. Mapping Data      — Mapping bulan Indonesia ke angka, traffic level kategorisasi
4. Agregasi Data     — Agregasi mingguan ke bulanan untuk input SARIMA
"""

import pandas as pd
import numpy as np
import os
import warnings
warnings.filterwarnings('ignore')

# ============================================================
# KONFIGURASI PATH
# ============================================================
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
RAW_DATA_PATH = os.path.join(BASE_DIR, 'data', 'raw', 'Drafting Data Bunihayu Rev.csv')
PROCESSED_DIR = os.path.join(BASE_DIR, 'data', 'processed')

# Buat folder processed jika belum ada
os.makedirs(PROCESSED_DIR, exist_ok=True)

# ============================================================
# MAPPING BULAN INDONESIA -> ANGKA
# ============================================================
BULAN_MAPPING = {
    'Januari': 1, 'Februari': 2, 'Maret': 3,
    'April': 4, 'Mei': 5, 'Juni': 6,
    'Juli': 7, 'Agustus': 8, 'September': 9,
    'Oktober': 10, 'November': 11, 'Desember': 12
}

# ============================================================
# MAPPING TRAFFIC LEVEL
# ============================================================
def map_traffic_level(total_visitors):
    """Mapping kategori traffic level berdasarkan jumlah kunjungan."""
    if pd.isna(total_visitors) or total_visitors == 0:
        return 'No Data'
    elif total_visitors < 50:
        return 'Low Traffic'
    elif total_visitors < 100:
        return 'Medium Traffic'
    elif total_visitors < 200:
        return 'High Traffic'
    else:
        return 'Peak Demand'


# ============================================================
# TAHAP 1: DATA CLEANING
# ============================================================
def cleaning_data(filepath=RAW_DATA_PATH):
    """
    Tahap 1: Data Cleaning
    - Load CSV mentah
    - Forward-fill kolom Tahun dan Bulan yang kosong (efek merged cells)
    - Hapus baris yang sepenuhnya kosong
    - Handle string 'RAMADAN' di kolom KTM
    - Handle missing values di bulan Maret (Ramadan)
    - Validasi tipe data (pastikan numerik)
    """
    print("=" * 60)
    print("TAHAP 1: DATA CLEANING")
    print("=" * 60)

    # 1. Load CSV
    df = pd.read_csv(filepath)
    print(f"\n[1.1] Data mentah dimuat: {df.shape[0]} baris, {df.shape[1]} kolom")
    print(f"      Kolom: {list(df.columns)}")

    # 2. Forward-fill Tahun dan Bulan (merged cells menyebabkan NaN)
    df['Tahun'] = df['Tahun'].ffill()
    df['Bulan'] = df['Bulan'].ffill()
    print(f"[1.2] Forward-fill Tahun & Bulan selesai")

    # 3. Konversi Tahun ke integer
    df['Tahun'] = df['Tahun'].astype(int)

    # 4. Handle string 'RAMADAN' di kolom KTM
    ramadan_mask = df['KTM (Karcis Tiket Masuk) '] == 'RAMADAN'
    ramadan_count = ramadan_mask.sum()
    df.loc[ramadan_mask, 'KTM (Karcis Tiket Masuk) '] = np.nan
    print(f"[1.3] Ditemukan {ramadan_count} baris 'RAMADAN' — diganti NaN")

    # 5. Konversi kolom numerik
    numeric_cols = ['KTM (Karcis Tiket Masuk) ', 'Glamping', 'Jumlah']
    for col in numeric_cols:
        df[col] = pd.to_numeric(df[col], errors='coerce')

    # 6. Identifikasi baris Maret/Ramadan (semua data kosong)
    maret_mask = df['Bulan'] == 'Maret'
    maret_rows = df[maret_mask]
    print(f"[1.4] Baris bulan Maret (Ramadan): {len(maret_rows)} baris — data kosong")

    # 7. Hapus baris Maret yang datanya semua NaN (akan di-interpolasi nanti)
    df_clean = df[~maret_mask].copy()
    print(f"[1.5] Baris Maret dihapus sementara (akan di-interpolasi setelah agregasi)")

    # 8. Cek dan hapus baris yang sepenuhnya kosong pada kolom data
    null_rows = df_clean[numeric_cols].isnull().all(axis=1)
    if null_rows.any():
        df_clean = df_clean[~null_rows]
        print(f"[1.6] Dihapus {null_rows.sum()} baris yang sepenuhnya kosong")
    else:
        print(f"[1.6] Tidak ada baris data yang sepenuhnya kosong")

    # 9. Cek missing values tersisa
    missing = df_clean[numeric_cols].isnull().sum()
    print(f"\n[1.7] Missing values setelah cleaning:")
    for col in numeric_cols:
        print(f"      {col}: {missing[col]}")

    # 10. Reset index
    df_clean = df_clean.reset_index(drop=True)

    print(f"\n[1.8] Hasil cleaning: {df_clean.shape[0]} baris, {df_clean.shape[1]} kolom")

    # Simpan hasil cleaning
    output_path = os.path.join(PROCESSED_DIR, 'cleaned_data.csv')
    df_clean.to_csv(output_path, index=False)
    print(f"[1.9] Disimpan ke: {output_path}")

    return df_clean


# ============================================================
# TAHAP 2: TRANSFORMASI DATA
# ============================================================
def transformasi_data(df):
    """
    Tahap 2: Transformasi Data
    - Mapping bulan Indonesia ke angka
    - Buat datetime index dari Tahun + Bulan + Minggu
    - Set time series frequency
    """
    print("\n" + "=" * 60)
    print("TAHAP 2: TRANSFORMASI DATA")
    print("=" * 60)

    # 1. Mapping bulan ke angka
    df['Bulan_Num'] = df['Bulan'].map(BULAN_MAPPING)
    print(f"\n[2.1] Mapping bulan ke angka selesai")
    print(f"      Bulan unik: {df['Bulan'].unique().tolist()}")

    # 2. Buat kolom Date dari Tahun + Bulan + Minggu
    # Setiap minggu diasumsikan mulai dari hari ke-1, 8, 15, 22, 29
    week_day_map = {1: 1, 2: 8, 3: 15, 4: 22, 5: 29}
    
    def create_date(row):
        year = int(row['Tahun'])
        month = int(row['Bulan_Num'])
        week = int(row['Minggu'])
        day = week_day_map.get(week, 1)
        # Handle hari 29 di bulan Februari
        if month == 2 and day > 28:
            day = 28
        try:
            return pd.Timestamp(year=year, month=month, day=day)
        except ValueError:
            # Fallback jika tanggal invalid
            return pd.Timestamp(year=year, month=month, day=28)

    df['Date'] = df.apply(create_date, axis=1)
    print(f"[2.2] Kolom Date dibuat dari Tahun + Bulan + Minggu")

    # 3. Sort berdasarkan Date
    df = df.sort_values('Date').reset_index(drop=True)
    print(f"[2.3] Data diurutkan berdasarkan Date")
    print(f"      Range: {df['Date'].min()} — {df['Date'].max()}")

    # 4. Set Date sebagai index
    df_ts = df.set_index('Date')
    print(f"[2.4] Date di-set sebagai index")

    # Simpan hasil transformasi
    output_path = os.path.join(PROCESSED_DIR, 'transformed_data.csv')
    df_ts.to_csv(output_path)
    print(f"[2.5] Disimpan ke: {output_path}")

    return df_ts


# ============================================================
# TAHAP 3: MAPPING DATA
# ============================================================
def mapping_data(df):
    """
    Tahap 3: Mapping Data
    - Tambah kolom traffic_level berdasarkan jumlah kunjungan
    - Mapping kategori traffic
    """
    print("\n" + "=" * 60)
    print("TAHAP 3: MAPPING DATA")
    print("=" * 60)

    # 1. Mapping traffic level
    df['Traffic_Level'] = df['Jumlah'].apply(map_traffic_level)
    print(f"\n[3.1] Traffic level mapping:")
    traffic_dist = df['Traffic_Level'].value_counts()
    for level, count in traffic_dist.items():
        print(f"      {level}: {count} minggu")

    # 2. Tambah kolom bulan dan tahun terpisah (untuk analisis)
    if 'Bulan_Num' not in df.columns:
        df['Bulan_Num'] = df['Bulan'].map(BULAN_MAPPING)

    # 3. Rename kolom agar lebih rapi
    df = df.rename(columns={
        'KTM (Karcis Tiket Masuk) ': 'KTM',
        'Jumlah': 'Total_Visitors'
    })
    print(f"[3.2] Kolom di-rename: KTM, Total_Visitors")

    # Simpan hasil mapping
    output_path = os.path.join(PROCESSED_DIR, 'mapped_data.csv')
    df.to_csv(output_path)
    print(f"[3.3] Disimpan ke: {output_path}")

    return df


# ============================================================
# TAHAP 4: AGREGASI DATA
# ============================================================
def agregasi_data(df):
    """
    Tahap 4: Agregasi Data
    - Agregasi data mingguan ke bulanan (sum per bulan)
    - Interpolasi bulan Maret (Ramadan) yang kosong
    - Buat time series bulanan yang kontinu
    """
    print("\n" + "=" * 60)
    print("TAHAP 4: AGREGASI DATA")
    print("=" * 60)

    # 1. Agregasi ke bulanan (sum)
    # Group by Tahun dan Bulan_Num, lalu sum
    monthly = df.groupby(['Tahun', 'Bulan_Num']).agg({
        'KTM': 'sum',
        'Glamping': 'sum',
        'Total_Visitors': 'sum',
        'Bulan': 'first',  # nama bulan
        'Minggu': 'count'  # jumlah minggu dalam bulan tersebut
    }).reset_index()
    
    monthly = monthly.rename(columns={'Minggu': 'Weeks_Count'})
    print(f"\n[4.1] Agregasi mingguan -> bulanan: {len(monthly)} bulan")

    # 2. Buat datetime index bulanan
    monthly['Date'] = pd.to_datetime(
        monthly['Tahun'].astype(str) + '-' + monthly['Bulan_Num'].astype(str) + '-01'
    )
    monthly = monthly.sort_values('Date').reset_index(drop=True)
    monthly = monthly.set_index('Date')
    
    # 3. Reindex untuk memastikan semua bulan ada (termasuk Maret yang kosong)
    full_date_range = pd.date_range(
        start=monthly.index.min(),
        end=monthly.index.max(),
        freq='MS'  # Month Start
    )
    monthly = monthly.reindex(full_date_range)
    monthly.index.name = 'Date'
    print(f"[4.2] Reindex ke range penuh: {len(monthly)} bulan")

    # 4. Identifikasi bulan yang kosong (Maret/Ramadan)
    missing_months = monthly[monthly['Total_Visitors'].isnull()]
    print(f"[4.3] Bulan kosong (Ramadan): {len(missing_months)}")
    for idx in missing_months.index:
        print(f"      {idx.strftime('%Y-%m')}")

    # 5. Interpolasi bulan Maret dengan rata-rata Februari & April
    numeric_cols = ['KTM', 'Glamping', 'Total_Visitors']
    monthly[numeric_cols] = monthly[numeric_cols].interpolate(method='linear')
    
    # Fill kolom non-numerik untuk bulan Maret
    monthly['Tahun'] = monthly.index.year
    monthly['Bulan_Num'] = monthly.index.month
    monthly['Bulan'] = monthly['Bulan_Num'].map({v: k for k, v in BULAN_MAPPING.items()})
    monthly['Weeks_Count'] = monthly['Weeks_Count'].fillna(4)  # default 4 minggu

    # Round to integer
    for col in numeric_cols:
        monthly[col] = monthly[col].round(0).astype(int)

    print(f"[4.4] Interpolasi bulan kosong selesai (linear interpolation)")

    # 6. Tambah traffic level bulanan
    monthly['Traffic_Level'] = monthly['Total_Visitors'].apply(
        lambda x: 'Low Traffic' if x < 150 else
                  'Medium Traffic' if x < 300 else
                  'High Traffic' if x < 500 else
                  'Peak Demand'
    )

    # 7. Tampilkan ringkasan
    print(f"\n[4.5] Ringkasan data bulanan:")
    print(f"      Total bulan: {len(monthly)}")
    print(f"      Range: {monthly.index.min().strftime('%Y-%m')} — {monthly.index.max().strftime('%Y-%m')}")
    print(f"      Total visitors keseluruhan: {monthly['Total_Visitors'].sum():,}")
    print(f"      Rata-rata per bulan: {monthly['Total_Visitors'].mean():.0f}")
    print(f"      Bulan tertinggi: {monthly['Total_Visitors'].idxmax().strftime('%Y-%m')} ({monthly['Total_Visitors'].max():,})")
    print(f"      Bulan terendah: {monthly['Total_Visitors'].idxmin().strftime('%Y-%m')} ({monthly['Total_Visitors'].min():,})")

    # Simpan hasil agregasi
    output_path = os.path.join(PROCESSED_DIR, 'monthly_aggregated.csv')
    monthly.to_csv(output_path)
    print(f"\n[4.6] Disimpan ke: {output_path}")

    return monthly


# ============================================================
# MAIN — Jalankan semua tahap preprocessing
# ============================================================
def run_preprocessing():
    """Jalankan keseluruhan pipeline preprocessing."""
    print("\n" + "=" * 60)
    print("PREPROCESSING DATA KUNJUNGAN WISATA BUNIHAYU")
    print("=" * 60)
    print(f"Input: {RAW_DATA_PATH}\n")

    # Tahap 1: Cleaning
    df_clean = cleaning_data()

    # Tahap 2: Transformasi
    df_transformed = transformasi_data(df_clean)

    # Tahap 3: Mapping
    df_mapped = mapping_data(df_transformed)

    # Tahap 4: Agregasi
    df_monthly = agregasi_data(df_mapped)

    print("\n" + "=" * 60)
    print("PREPROCESSING SELESAI!")
    print("=" * 60)
    print(f"\nFile output tersedia di: {PROCESSED_DIR}")
    print(f"  1. cleaned_data.csv       — hasil cleaning")
    print(f"  2. transformed_data.csv   — hasil transformasi")
    print(f"  3. mapped_data.csv        — hasil mapping")
    print(f"  4. monthly_aggregated.csv — hasil agregasi (input SARIMA)")

    return df_monthly


if __name__ == '__main__':
    df = run_preprocessing()
    print("\n\nPreview data bulanan (5 baris pertama):")
    print(df.head().to_string())
