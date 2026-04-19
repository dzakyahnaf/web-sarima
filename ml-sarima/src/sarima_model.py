"""
SARIMA Model Module  -  Sistem Prediksi Kunjungan Wisata Bunihayu
================================================================
- Stationarity testing (ADF Test)
- ACF/PACF analysis
- Auto SARIMA parameter selection
- Model training & fitting
- Forecasting
"""

import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
from statsmodels.tsa.statespace.sarimax import SARIMAX
from statsmodels.tsa.stattools import adfuller
from statsmodels.graphics.tsaplots import plot_acf, plot_pacf
import pmdarima as pm
import joblib
import json
import os
import warnings
warnings.filterwarnings('ignore')

# Konfigurasi
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PROCESSED_DIR = os.path.join(BASE_DIR, 'data', 'processed')
MODELS_DIR = os.path.join(BASE_DIR, 'models')
OUTPUT_DIR = os.path.join(BASE_DIR, 'output')
PLOTS_DIR = os.path.join(OUTPUT_DIR, 'plots')

for d in [MODELS_DIR, OUTPUT_DIR, PLOTS_DIR]:
    os.makedirs(d, exist_ok=True)


# ============================================================
# STATIONARITY TEST
# ============================================================
def adf_test(series, title='Time Series'):
    """
    Augmented Dickey-Fuller (ADF) Test untuk stationarity.
    H0: Series memiliki unit root (non-stationary)
    H1: Series stationary
    """
    print(f"\n  ADF Test  -  {title}")
    print(f"  {'-'*40}")
    
    result = adfuller(series.dropna(), autolag='AIC')
    
    labels = ['ADF Statistic', 'p-value', '# Lags Used', '# Observations']
    for label, value in zip(labels, result[:4]):
        print(f"    {label:20s}: {value:.6f}" if isinstance(value, float) else f"    {label:20s}: {value}")
    
    print(f"    {'Critical Values':20s}:")
    for key, value in result[4].items():
        print(f"      {key}: {value:.6f}")
    
    is_stationary = result[1] < 0.05
    status = "STATIONARY [OK]" if is_stationary else "NON-STATIONARY [X]"
    conclusion_sign = '<' if is_stationary else '>'
    print(f"    {'Conclusion':20s}: {status} (p-value {conclusion_sign} 0.05)")
    
    return {
        'adf_statistic': result[0],
        'p_value': result[1],
        'lags_used': result[2],
        'n_observations': result[3],
        'critical_values': result[4],
        'is_stationary': is_stationary
    }


# ============================================================
# ACF / PACF PLOTS
# ============================================================
def plot_acf_pacf(series, lags=12, title=''):
    """Plot ACF dan PACF untuk parameter selection."""
    fig, axes = plt.subplots(1, 2, figsize=(14, 5))
    fig.suptitle(f'ACF & PACF  -  {title}', fontsize=14, fontweight='bold')
    
    plot_acf(series.dropna(), lags=lags, ax=axes[0], alpha=0.05)
    axes[0].set_title('Autocorrelation Function (ACF)')
    
    plot_pacf(series.dropna(), lags=lags, ax=axes[1], alpha=0.05, method='ywm')
    axes[1].set_title('Partial Autocorrelation Function (PACF)')
    
    for ax in axes:
        ax.grid(True, alpha=0.3)
    
    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'acf_pacf.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


# ============================================================
# AUTO SARIMA  -  PARAMETER SELECTION
# ============================================================
def find_optimal_parameters(series, seasonal_period=12):
    """
    Gunakan pmdarima auto_arima untuk menemukan parameter optimal SARIMA.
    Dengan fallback untuk dataset kecil.
    Returns: best (p,d,q)(P,D,Q,s) parameters
    """
    print("\n" + "=" * 60)
    print("AUTO SARIMA  -  PARAMETER SELECTION")
    print("=" * 60)
    
    print(f"\n  Seasonal period (s): {seasonal_period}")
    print(f"  Jumlah data points : {len(series)}")
    print(f"  Mencari parameter optimal... (ini bisa memakan waktu)")
    
    # Untuk dataset kecil (< 3 * seasonal_period), kurangi seasonal period
    # dan set D=0 secara eksplisit untuk menghindari error differencing
    effective_s = seasonal_period
    if len(series) < 3 * seasonal_period:
        effective_s = max(4, len(series) // 4)  # minimal 4 (quarterly pattern)
        print(f"  [INFO] Data terlalu sedikit untuk s={seasonal_period}")
        print(f"         Menggunakan s={effective_s} (adaptive seasonal period)")
    
    try:
        # Coba auto_arima dengan D=0 (eksplisit, menghindari seasonal diff error)
        model = pm.auto_arima(
            series,
            start_p=0, start_q=0,
            max_p=3, max_q=3,
            d=None,           # auto-detect non-seasonal differencing
            start_P=0, start_Q=0,
            max_P=1, max_Q=1,
            D=0,              # Eksplisit: TIDAK seasonal differencing (data terlalu sedikit)
            m=effective_s,
            seasonal=True,
            trace=True,
            error_action='ignore',
            suppress_warnings=True,
            stepwise=True,
            n_fits=50
        )
        
        order = model.order
        seasonal_order = model.seasonal_order
        
    except Exception as e:
        print(f"\n  [WARNING] Auto SARIMA gagal: {e}")
        print(f"  Menggunakan parameter default SARIMA(1,1,1)(1,0,1,{effective_s})")
        
        # Fallback: parameter konservatif
        order = (1, 1, 1)
        seasonal_order = (1, 0, 1, effective_s)
        model = None
    
    print(f"\n  {'='*40}")
    print(f"  Parameter Optimal Ditemukan!")
    print(f"  {'='*40}")
    print(f"  SARIMA Order        : {order}")
    print(f"  Seasonal Order      : {seasonal_order}")
    if model:
        print(f"  AIC                 : {model.aic():.2f}")
        print(f"  BIC                 : {model.bic():.2f}")
    
    return order, seasonal_order, model


# ============================================================
# SARIMA MODEL TRAINING
# ============================================================
def train_sarima(series, order, seasonal_order, train_ratio=0.8):
    """
    Train SARIMA model dengan train/test split.
    
    Args:
        series: Time series data (monthly visitors)
        order: (p, d, q)
        seasonal_order: (P, D, Q, s)
        train_ratio: Proporsi data training
    """
    print("\n" + "=" * 60)
    print("SARIMA MODEL TRAINING")
    print("=" * 60)
    
    # Train/Test Split
    n = len(series)
    train_size = int(n * train_ratio)
    train = series[:train_size]
    test = series[train_size:]
    
    print(f"\n  Total data    : {n} bulan")
    print(f"  Training data : {len(train)} bulan ({train.index[0].strftime('%b %Y')}  -  {train.index[-1].strftime('%b %Y')})")
    print(f"  Testing data  : {len(test)} bulan ({test.index[0].strftime('%b %Y')}  -  {test.index[-1].strftime('%b %Y')})")
    print(f"\n  SARIMA{order}x{seasonal_order}")
    print(f"  Fitting model...")
    
    # Fit SARIMA
    model = SARIMAX(
        train,
        order=order,
        seasonal_order=seasonal_order,
        trend='c',
        enforce_stationarity=False,
        enforce_invertibility=False
    )
    
    fitted_model = model.fit(disp=False)
    
    print(f"\n  Model Summary:")
    print(f"  {'-'*40}")
    print(f"  AIC  : {fitted_model.aic:.2f}")
    print(f"  BIC  : {fitted_model.bic:.2f}")
    print(f"  HQIC : {fitted_model.hqic:.2f}")
    
    # Predictions on test set
    predictions = fitted_model.forecast(steps=len(test))
    predictions.index = test.index
    
    # Ensure no negative predictions
    predictions = predictions.clip(lower=0)
    
    return fitted_model, train, test, predictions


# ============================================================
# FORECASTING
# ============================================================
def forecast_future(fitted_model, series, months_ahead=12):
    """
    Forecast kunjungan N bulan ke depan.
    """
    print("\n" + "=" * 60)
    print(f"FORECASTING  -  {months_ahead} Bulan ke Depan")
    print("=" * 60)
    
    # Refit model pada seluruh data
    full_model = SARIMAX(
        series,
        order=fitted_model.specification['order'],
        seasonal_order=fitted_model.specification['seasonal_order'],
        trend='c',
        enforce_stationarity=False,
        enforce_invertibility=False
    )
    full_fitted = full_model.fit(disp=False)
    
    # Forecast
    forecast_result = full_fitted.get_forecast(steps=months_ahead)
    forecast_mean = forecast_result.predicted_mean.clip(lower=0)
    forecast_ci = forecast_result.conf_int()
    forecast_ci = forecast_ci.clip(lower=0)
    
    # Create forecast DataFrame
    forecast_df = pd.DataFrame({
        'Predicted_Visitors': forecast_mean.round(0).astype(int),
        'Lower_CI': forecast_ci.iloc[:, 0].round(0).astype(int),
        'Upper_CI': forecast_ci.iloc[:, 1].round(0).astype(int),
    })
    forecast_df.index.name = 'Date'
    
    # Add month names
    bulan_names = {1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
                   5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
                   9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'}
    forecast_df['Bulan'] = forecast_df.index.month.map(bulan_names)
    forecast_df['Tahun'] = forecast_df.index.year
    
    # Confidence percentage (normalized)
    range_width = forecast_df['Upper_CI'] - forecast_df['Lower_CI']
    max_range = range_width.max() if range_width.max() > 0 else 1
    forecast_df['Confidence_Pct'] = ((1 - range_width / max_range) * 30 + 70).round(1)
    
    print(f"\n  Hasil Forecast:")
    print(f"  {'-'*60}")
    for idx, row in forecast_df.iterrows():
        print(f"  {idx.strftime('%b %Y'):>10s} | "
              f"Prediksi: {row['Predicted_Visitors']:>5,} | "
              f"CI: [{row['Lower_CI']:>5,}  -  {row['Upper_CI']:>5,}] | "
              f"Conf: {row['Confidence_Pct']:.0f}%")
    
    return forecast_df, full_fitted


# ============================================================
# VISUALIZATION  -  ACTUAL VS PREDICTED
# ============================================================
def plot_actual_vs_predicted(train, test, predictions, title=''):
    """Plot actual vs predicted pada test set."""
    fig, ax = plt.subplots(figsize=(14, 6))
    
    ax.plot(train.index, train, color='#1B4F72', linewidth=2, label='Training Data', alpha=0.8)
    ax.plot(test.index, test, color='#27AE60', linewidth=2, marker='o', markersize=5, label='Actual (Test)')
    ax.plot(predictions.index, predictions, color='#E74C3C', linewidth=2, marker='s', markersize=5, 
            linestyle='--', label='Predicted')
    
    ax.fill_between(test.index, test, predictions, alpha=0.15, color='red')
    
    ax.set_title(f'SARIMA  -  Actual vs Predicted {title}', fontsize=14, fontweight='bold')
    ax.set_xlabel('Bulan')
    ax.set_ylabel('Jumlah Kunjungan')
    ax.legend(fontsize=10)
    ax.grid(True, alpha=0.3)
    ax.xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'actual_vs_predicted.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def plot_forecast(series, forecast_df, title=''):
    """Plot historical data + future forecast."""
    fig, ax = plt.subplots(figsize=(14, 7))
    
    # Historical
    ax.plot(series.index, series, color='#1B4F72', linewidth=2, label='Data Historis', alpha=0.9)
    ax.fill_between(series.index, series, alpha=0.1, color='#1B4F72')
    
    # Forecast
    ax.plot(forecast_df.index, forecast_df['Predicted_Visitors'], 
            color='#E74C3C', linewidth=2.5, marker='o', markersize=6, 
            linestyle='--', label='Prediksi SARIMA')
    
    # Confidence interval
    ax.fill_between(forecast_df.index,
                     forecast_df['Lower_CI'],
                     forecast_df['Upper_CI'],
                     alpha=0.2, color='#E74C3C', label='95% Confidence Interval')
    
    # Vertical line separator
    ax.axvline(x=series.index[-1], color='gray', linestyle=':', linewidth=1.5, alpha=0.7)
    ax.text(series.index[-1], ax.get_ylim()[1] * 0.95, '  Forecast -->', 
            fontsize=10, color='gray', va='top')
    
    ax.set_title(f'Prediksi Kunjungan Wisata Bunihayu  -  SARIMA {title}', fontsize=14, fontweight='bold')
    ax.set_xlabel('Bulan')
    ax.set_ylabel('Jumlah Kunjungan')
    ax.legend(fontsize=10, loc='upper left')
    ax.grid(True, alpha=0.3)
    ax.xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))
    ax.xaxis.set_major_locator(mdates.MonthLocator(interval=2))

    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'forecast.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


def plot_residuals(fitted_model):
    """Plot residual analysis."""
    residuals = fitted_model.resid
    
    fig, axes = plt.subplots(2, 2, figsize=(14, 10))
    fig.suptitle('Residual Analysis  -  SARIMA Model', fontsize=14, fontweight='bold')
    
    # Residuals over time
    axes[0, 0].plot(residuals.index, residuals, color='#1B4F72', linewidth=1)
    axes[0, 0].axhline(y=0, color='red', linestyle='--', linewidth=1)
    axes[0, 0].set_title('Residuals over Time')
    axes[0, 0].grid(True, alpha=0.3)
    
    # Histogram
    axes[0, 1].hist(residuals, bins=12, color='#2E86C1', alpha=0.7, edgecolor='white')
    axes[0, 1].set_title('Residual Distribution')
    axes[0, 1].grid(axis='y', alpha=0.3)
    
    # Q-Q Plot
    from scipy import stats
    stats.probplot(residuals.dropna(), dist="norm", plot=axes[1, 0])
    axes[1, 0].set_title('Q-Q Plot')
    axes[1, 0].grid(True, alpha=0.3)
    
    # ACF of residuals
    plot_acf(residuals.dropna(), lags=min(12, len(residuals)//2 - 1), ax=axes[1, 1], alpha=0.05)
    axes[1, 1].set_title('ACF of Residuals')
    axes[1, 1].grid(True, alpha=0.3)
    
    plt.tight_layout()
    path = os.path.join(PLOTS_DIR, 'residuals.png')
    plt.savefig(path, dpi=150, bbox_inches='tight')
    plt.close()
    print(f"  Saved: {path}")


# ============================================================
# EXPORT MODEL & RESULTS
# ============================================================
def export_results(fitted_model, forecast_df, metrics, order, seasonal_order):
    """Export model dan hasil ke file."""
    print("\n" + "=" * 60)
    print("EXPORT MODEL & RESULTS")
    print("=" * 60)
    
    # 1. Save trained model (pickle)
    model_path = os.path.join(MODELS_DIR, 'sarima_model.pkl')
    joblib.dump(fitted_model, model_path)
    print(f"\n  Model saved: {model_path}")
    
    # 2. Save predictions as CSV
    pred_csv_path = os.path.join(OUTPUT_DIR, 'predictions.csv')
    forecast_df.to_csv(pred_csv_path)
    print(f"  Predictions CSV: {pred_csv_path}")
    
    # 3. Save predictions as JSON (for web)
    pred_json = {
        'predictions': [],
        'model_info': {
            'order': list(order),
            'seasonal_order': list(seasonal_order),
            'mae': round(metrics['mae'], 2),
            'rmse': round(metrics['rmse'], 2),
            'mape': round(metrics['mape'], 2),
            'aic': round(fitted_model.aic, 2),
            'bic': round(fitted_model.bic, 2),
        }
    }
    
    for idx, row in forecast_df.iterrows():
        pred_json['predictions'].append({
            'date': idx.strftime('%Y-%m-%d'),
            'month': row['Bulan'],
            'year': int(row['Tahun']),
            'predicted_visitors': int(row['Predicted_Visitors']),
            'lower_ci': int(row['Lower_CI']),
            'upper_ci': int(row['Upper_CI']),
            'confidence_pct': float(row['Confidence_Pct'])
        })
    
    pred_json_path = os.path.join(OUTPUT_DIR, 'predictions.json')
    with open(pred_json_path, 'w') as f:
        json.dump(pred_json, f, indent=2, ensure_ascii=False)
    print(f"  Predictions JSON: {pred_json_path}")
    
    # 4. Save model info
    info = {
        'sarima_order': list(order),
        'seasonal_order': list(seasonal_order),
        'metrics': {
            'mae': round(metrics['mae'], 2),
            'rmse': round(metrics['rmse'], 2),
            'mape': round(metrics['mape'], 2),
        },
        'aic': round(fitted_model.aic, 2),
        'bic': round(fitted_model.bic, 2),
        'n_observations': fitted_model.nobs,
    }
    
    info_path = os.path.join(OUTPUT_DIR, 'model_info.json')
    with open(info_path, 'w') as f:
        json.dump(info, f, indent=2)
    print(f"  Model info JSON: {info_path}")


# ============================================================
# MAIN  -  FULL SARIMA PIPELINE
# ============================================================
def run_sarima_pipeline(forecast_months=12):
    """Jalankan keseluruhan pipeline SARIMA."""
    from evaluation import evaluate_model
    
    print("\n" + "=" * 60)
    print("SARIMA MODEL PIPELINE")
    print("Prediksi Kunjungan Wisata Bunihayu")
    print("=" * 60)
    
    # 1. Load monthly aggregated data
    data_path = os.path.join(PROCESSED_DIR, 'monthly_aggregated.csv')
    df = pd.read_csv(data_path, index_col=0, parse_dates=True)
    series = df['Total_Visitors']
    
    print(f"\n  Data loaded: {len(series)} bulan")
    print(f"  Range: {series.index[0].strftime('%b %Y')}  -  {series.index[-1].strftime('%b %Y')}")
    
    # 2. Stationarity Test
    print("\n" + "=" * 60)
    print("STATIONARITY TEST")
    print("=" * 60)
    
    adf_original = adf_test(series, 'Original Series')
    
    if not adf_original['is_stationary']:
        print("\n  -> Series non-stationary, perlu differencing")
        series_diff = series.diff().dropna()
        adf_diff = adf_test(series_diff, 'First Differencing')
    
    # 3. ACF/PACF Plot
    print("\n  Generating ACF/PACF plots...")
    plot_acf_pacf(series, lags=min(12, len(series)//2 - 1), title='Total Visitors (Monthly)')
    
    # 4. Auto SARIMA  -  Find optimal parameters
    # Gunakan seasonal period yang sesuai dengan data
    # Karena data hanya 2 tahun, gunakan s yang lebih kecil jika perlu
    s = min(12, len(series) // 2)  # Pastikan cukup data untuk seasonal
    order, seasonal_order, auto_model = find_optimal_parameters(series, seasonal_period=s)
    
    # 5. Train SARIMA model
    fitted_model, train, test, predictions = train_sarima(
        series, order, seasonal_order, train_ratio=0.8
    )
    
    # 6. Evaluate model
    metrics = evaluate_model(test, predictions)
    
    # 7. Plot actual vs predicted
    print("\n  Generating plots...")
    plot_actual_vs_predicted(train, test, predictions)
    plot_residuals(fitted_model)
    
    # 8. Forecast future
    forecast_df, full_fitted = forecast_future(fitted_model, series, months_ahead=forecast_months)
    plot_forecast(series, forecast_df, f"SARIMA{order}x{seasonal_order}")
    
    # 9. Export results
    export_results(full_fitted, forecast_df, metrics, order, seasonal_order)
    
    print("\n" + "=" * 60)
    print("SARIMA PIPELINE SELESAI!")
    print("=" * 60)
    
    return full_fitted, forecast_df, metrics


if __name__ == '__main__':
    fitted_model, forecast_df, metrics = run_sarima_pipeline(forecast_months=12)
