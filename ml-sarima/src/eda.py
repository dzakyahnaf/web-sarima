"""
Exploratory Data Analysis (EDA) Module
======================================
Visualisasi dan analisis data kunjungan wisata Bunihayu
- Plot raw data mingguan
- Seasonal decomposition 
- Monthly trend analysis
- Distribusi kunjungan
- Korelasi antar variabel
"""

import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
import seaborn as sns
from statsmodels.tsa.seasonal import seasonal_decompose
import os
import warnings
warnings.filterwarnings('ignore')

# Konfigurasi
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PROCESSED_DIR = os.path.join(BASE_DIR, 'data', 'processed')
PLOTS_DIR = os.path.join(BASE_DIR, 'output', 'plots')
os.makedirs(PLOTS_DIR, exist_ok=True)

# Style
plt.style.use('seaborn-v0_8-whitegrid')
sns.set_palette('husl')
COLORS = {
    'primary': '#1B4F72',
    'secondary': '#2E86C1',
    'accent': '#27AE60',
    'warning': '#F39C12',
    'danger': '#E74C3C',
    'light': '#85C1E9'
}


def plot_weekly_raw_data(df_weekly):
    """Plot data mingguan mentah (sebelum agregasi)."""
    fig, axes = plt.subplots(3, 1, figsize=(14, 10), sharex=True)
    fig.suptitle('Data Kunjungan Mingguan Wisata Bunihayu\n(Data Mentah)', 
                 fontsize=14, fontweight='bold')

    # Total Visitors
    axes[0].plot(df_weekly.index, df_weekly['Total_Visitors'], 
                 color=COLORS['primary'], linewidth=1.5, marker='o', markersize=3)
    axes[0].fill_between(df_weekly.index, df_weekly['Total_Visitors'], alpha=0.15, color=COLORS['primary'])
    axes[0].set_ylabel('Total Visitors')
    axes[0].set_title('Total Kunjungan per Minggu')

    # KTM
    axes[1].plot(df_weekly.index, df_weekly['KTM'],
                 color=COLORS['secondary'], linewidth=1.5, marker='s', markersize=3)
    axes[1].fill_between(df_weekly.index, df_weekly['KTM'], alpha=0.15, color=COLORS['secondary'])
    axes[1].set_ylabel('Tiket Masuk')
    axes[1].set_title('Karcis Tiket Masuk (KTM) per Minggu')

    # Glamping
    axes[2].plot(df_weekly.index, df_weekly['Glamping'],
                 color=COLORS['accent'], linewidth=1.5, marker='^', markersize=3)
    axes[2].fill_between(df_weekly.index, df_weekly['Glamping'], alpha=0.15, color=COLORS['accent'])
    axes[2].set_ylabel('Glamping')
    axes[2].set_title('Kunjungan Glamping per Minggu')
    axes[2].set_xlabel('Tanggal')

    for ax in axes:
        ax.xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))
        ax.xaxis.set_major_locator(mdates.MonthLocator(interval=2))
        ax.grid(True, alpha=0.3)

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'raw_data_weekly.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def plot_monthly_trend(df_monthly):
    """Plot data bulanan yang sudah diagregasi."""
    fig, ax = plt.subplots(figsize=(14, 6))
    
    ax.plot(df_monthly.index, df_monthly['Total_Visitors'], 
            color=COLORS['primary'], linewidth=2.5, marker='o', markersize=6, label='Total Visitors')
    ax.fill_between(df_monthly.index, df_monthly['Total_Visitors'], 
                     alpha=0.15, color=COLORS['primary'])
    
    # Highlight peak & low
    peak_idx = df_monthly['Total_Visitors'].idxmax()
    low_idx = df_monthly['Total_Visitors'].idxmin()
    ax.annotate(f"Peak: {df_monthly.loc[peak_idx, 'Total_Visitors']:,}",
                xy=(peak_idx, df_monthly.loc[peak_idx, 'Total_Visitors']),
                xytext=(15, 15), textcoords='offset points',
                arrowprops=dict(arrowstyle='->', color=COLORS['danger']),
                fontsize=9, color=COLORS['danger'], fontweight='bold')
    ax.annotate(f"Low: {df_monthly.loc[low_idx, 'Total_Visitors']:,}",
                xy=(low_idx, df_monthly.loc[low_idx, 'Total_Visitors']),
                xytext=(15, -20), textcoords='offset points',
                arrowprops=dict(arrowstyle='->', color=COLORS['accent']),
                fontsize=9, color=COLORS['accent'], fontweight='bold')

    ax.set_title('Trend Kunjungan Wisata Bunihayu  -  Data Bulanan', fontsize=14, fontweight='bold')
    ax.set_xlabel('Bulan')
    ax.set_ylabel('Jumlah Kunjungan')
    ax.xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))
    ax.xaxis.set_major_locator(mdates.MonthLocator(interval=2))
    ax.legend()
    ax.grid(True, alpha=0.3)

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'monthly_trend.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def plot_seasonal_decomposition(df_monthly):
    """Seasonal decomposition (trend, seasonal, residual)."""
    ts = df_monthly['Total_Visitors']
    
    # Decompose  -  period=12 jika cukup data, else gunakan yang ada
    period = min(12, len(ts) // 2)
    if period < 2:
        print("  [SKIP] Data terlalu sedikit untuk seasonal decomposition")
        return
    
    decomposition = seasonal_decompose(ts, model='additive', period=period)

    fig, axes = plt.subplots(4, 1, figsize=(14, 12))
    fig.suptitle('Seasonal Decomposition  -  Kunjungan Wisata Bunihayu', 
                 fontsize=14, fontweight='bold')

    components = [
        ('Observed', decomposition.observed, COLORS['primary']),
        ('Trend', decomposition.trend, COLORS['secondary']),
        ('Seasonal', decomposition.seasonal, COLORS['accent']),
        ('Residual', decomposition.resid, COLORS['warning']),
    ]

    for ax, (name, data, color) in zip(axes, components):
        ax.plot(data.index, data, color=color, linewidth=1.5)
        if name != 'Residual':
            ax.fill_between(data.index, data, alpha=0.1, color=color)
        ax.set_ylabel(name)
        ax.set_title(name, fontsize=11, fontweight='bold')
        ax.grid(True, alpha=0.3)
        ax.xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'seasonal_decomposition.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def plot_monthly_comparison(df_monthly):
    """Perbandingan year-over-year per bulan."""
    df = df_monthly.copy()
    df['Year'] = df.index.year
    df['Month'] = df.index.month
    
    fig, ax = plt.subplots(figsize=(12, 6))
    
    years = df['Year'].unique()
    month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                   'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
    width = 0.35
    x = np.arange(12)
    
    colors = [COLORS['secondary'], COLORS['accent']]
    for i, year in enumerate(sorted(years)):
        year_data = df[df['Year'] == year].set_index('Month')['Total_Visitors']
        values = [year_data.get(m+1, 0) for m in range(12)]
        bars = ax.bar(x + i*width, values, width, label=str(year), 
                      color=colors[i % len(colors)], alpha=0.85, edgecolor='white')
        # Value labels
        for bar, val in zip(bars, values):
            if val > 0:
                ax.text(bar.get_x() + bar.get_width()/2., bar.get_height() + 5,
                       f'{int(val)}', ha='center', va='bottom', fontsize=7)

    ax.set_xlabel('Bulan')
    ax.set_ylabel('Jumlah Kunjungan')
    ax.set_title('Perbandingan Kunjungan per Bulan  -  Year over Year', fontsize=14, fontweight='bold')
    ax.set_xticks(x + width/2)
    ax.set_xticklabels(month_names)
    ax.legend()
    ax.grid(axis='y', alpha=0.3)

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'monthly_comparison_yoy.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def plot_distribution(df_monthly):
    """Distribusi jumlah kunjungan."""
    fig, axes = plt.subplots(1, 3, figsize=(16, 5))
    fig.suptitle('Distribusi Data Kunjungan Wisata Bunihayu', fontsize=14, fontweight='bold')

    cols = [('Total_Visitors', 'Total Kunjungan', COLORS['primary']),
            ('KTM', 'Tiket Masuk', COLORS['secondary']),
            ('Glamping', 'Glamping', COLORS['accent'])]

    for ax, (col, title, color) in zip(axes, cols):
        data = df_monthly[col].dropna()
        ax.hist(data, bins=10, color=color, alpha=0.7, edgecolor='white')
        ax.axvline(data.mean(), color=COLORS['danger'], linestyle='--', linewidth=1.5, label=f'Mean: {data.mean():.0f}')
        ax.axvline(data.median(), color=COLORS['warning'], linestyle='-.', linewidth=1.5, label=f'Median: {data.median():.0f}')
        ax.set_title(title)
        ax.set_xlabel('Jumlah')
        ax.set_ylabel('Frekuensi')
        ax.legend(fontsize=8)
        ax.grid(axis='y', alpha=0.3)

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'distribution.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def plot_correlation(df_monthly):
    """Heatmap korelasi antar variabel."""
    fig, ax = plt.subplots(figsize=(8, 6))
    
    corr_cols = ['KTM', 'Glamping', 'Total_Visitors']
    corr = df_monthly[corr_cols].corr()
    
    sns.heatmap(corr, annot=True, cmap='RdYlBu_r', center=0, 
                fmt='.3f', square=True, ax=ax,
                linewidths=0.5, linecolor='white',
                cbar_kws={'label': 'Korelasi'})
    ax.set_title('Heatmap Korelasi  -  Variabel Kunjungan', fontsize=14, fontweight='bold')

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'correlation_heatmap.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def plot_boxplot_monthly(df_monthly):
    """Boxplot kunjungan per bulan."""
    fig, ax = plt.subplots(figsize=(12, 6))
    
    df = df_monthly.copy()
    df['Month'] = df.index.month
    month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                   'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
    
    data = [df[df['Month'] == m+1]['Total_Visitors'].values for m in range(12)]
    bp = ax.boxplot(data, labels=month_names, patch_artist=True)
    
    colors_list = plt.cm.Set3(np.linspace(0, 1, 12))
    for patch, color in zip(bp['boxes'], colors_list):
        patch.set_facecolor(color)
        patch.set_alpha(0.7)

    ax.set_title('Distribusi Kunjungan per Bulan (Boxplot)', fontsize=14, fontweight='bold')
    ax.set_xlabel('Bulan')
    ax.set_ylabel('Jumlah Kunjungan')
    ax.grid(axis='y', alpha=0.3)

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'boxplot_monthly.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def print_summary_stats(df_monthly):
    """Print statistik ringkasan."""
    print("\n" + "=" * 60)
    print("RINGKASAN STATISTIK")
    print("=" * 60)
    
    tv = df_monthly['Total_Visitors']
    print(f"\n  Total data points    : {len(tv)} bulan")
    print(f"  Range tanggal        : {df_monthly.index.min().strftime('%b %Y')}  -  {df_monthly.index.max().strftime('%b %Y')}")
    print(f"  Total visitors       : {tv.sum():,.0f}")
    print(f"  Rata-rata/bulan      : {tv.mean():,.1f}")
    print(f"  Std deviasi          : {tv.std():,.1f}")
    print(f"  Minimum              : {tv.min():,.0f} ({tv.idxmin().strftime('%b %Y')})")
    print(f"  Maximum              : {tv.max():,.0f} ({tv.idxmax().strftime('%b %Y')})")
    print(f"  Median               : {tv.median():,.0f}")
    
    print(f"\n  Descriptive Statistics:")
    print(df_monthly[['KTM', 'Glamping', 'Total_Visitors']].describe().to_string())


def run_eda():
    """Jalankan semua analisis EDA."""
    print("\n" + "=" * 60)
    print("EXPLORATORY DATA ANALYSIS (EDA)")
    print("=" * 60)
    
    # Load data
    weekly_path = os.path.join(PROCESSED_DIR, 'mapped_data.csv')
    monthly_path = os.path.join(PROCESSED_DIR, 'monthly_aggregated.csv')
    
    df_weekly = pd.read_csv(weekly_path, index_col=0, parse_dates=True)
    df_monthly = pd.read_csv(monthly_path, index_col=0, parse_dates=True)
    
    print(f"\n  Data mingguan : {len(df_weekly)} baris")
    print(f"  Data bulanan  : {len(df_monthly)} baris")
    print(f"\nMembuat visualisasi...")
    
    # Generate plots
    plot_weekly_raw_data(df_weekly)
    plot_monthly_trend(df_monthly)
    plot_seasonal_decomposition(df_monthly)
    plot_monthly_comparison(df_monthly)
    plot_distribution(df_monthly)
    plot_correlation(df_monthly)
    plot_boxplot_monthly(df_monthly)
    
    # Print summary
    print_summary_stats(df_monthly)
    
    print(f"\n{'='*60}")
    print(f"EDA selesai! Semua plot tersimpan di: {PLOTS_DIR}")
    print(f"{'='*60}")


if __name__ == '__main__':
    run_eda()
