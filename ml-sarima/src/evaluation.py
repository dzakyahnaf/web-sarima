"""
Evaluation Module — Metrik Evaluasi Model SARIMA
==================================================
- MAE (Mean Absolute Error)
- RMSE (Root Mean Square Error)
- MAPE (Mean Absolute Percentage Error)
"""

import numpy as np
import pandas as pd
from sklearn.metrics import mean_absolute_error, mean_squared_error


def evaluate_model(actual, predicted):
    """
    Evaluasi performa model SARIMA.
    
    Args:
        actual: pd.Series — data aktual (test set)
        predicted: pd.Series — data prediksi
    
    Returns:
        dict dengan metrik MAE, RMSE, MAPE
    """
    print("\n" + "=" * 60)
    print("MODEL EVALUATION")
    print("=" * 60)
    
    actual = actual.values
    predicted = predicted.values
    
    # MAE
    mae = mean_absolute_error(actual, predicted)
    
    # RMSE
    rmse = np.sqrt(mean_squared_error(actual, predicted))
    
    # MAPE (handle division by zero)
    non_zero_mask = actual != 0
    if non_zero_mask.any():
        mape = np.mean(np.abs((actual[non_zero_mask] - predicted[non_zero_mask]) / actual[non_zero_mask])) * 100
    else:
        mape = float('inf')
    
    print(f"\n  {'Metrik':20s} {'Nilai':>10s} {'Interpretasi'}")
    print(f"  {'-'*60}")
    print(f"  {'MAE':20s} {mae:>10.2f}   Rata-rata error absolut per bulan")
    print(f"  {'RMSE':20s} {rmse:>10.2f}   Root mean square error")
    print(f"  {'MAPE':20s} {mape:>9.2f}%   Persentase error rata-rata")
    
    # Interpretasi MAPE
    if mape < 10:
        quality = "SANGAT BAIK"
    elif mape < 20:
        quality = "BAIK"
    elif mape < 50:
        quality = "CUKUP"
    else:
        quality = "KURANG AKURAT"
    
    print(f"\n  Kualitas Model: {quality} (berdasarkan MAPE)")
    
    metrics = {
        'mae': mae,
        'rmse': rmse,
        'mape': mape,
        'quality': quality
    }
    
    return metrics
