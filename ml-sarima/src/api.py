"""
Flask API — Endpoint untuk Integrasi Laravel
==============================================
Menyediakan REST API endpoints:
- GET  /api/predict       — Prediksi N bulan ke depan
- GET  /api/historical    — Data historis yang sudah diproses
- GET  /api/model-info    — Info model (accuracy, parameter)
- GET  /api/stats         — Statistik ringkasan
- POST /api/retrain       — Retrain model dengan data baru
"""

from flask import Flask, jsonify, request
from flask_cors import CORS
import pandas as pd
import numpy as np
import joblib
import json
import os

app = Flask(__name__)
CORS(app)

# Konfigurasi path
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODELS_DIR = os.path.join(BASE_DIR, 'models')
OUTPUT_DIR = os.path.join(BASE_DIR, 'output')
PROCESSED_DIR = os.path.join(BASE_DIR, 'data', 'processed')


def load_model():
    """Load trained SARIMA model."""
    model_path = os.path.join(MODELS_DIR, 'sarima_model.pkl')
    if os.path.exists(model_path):
        return joblib.load(model_path)
    return None


def load_predictions():
    """Load precomputed predictions."""
    pred_path = os.path.join(OUTPUT_DIR, 'predictions.json')
    if os.path.exists(pred_path):
        with open(pred_path, 'r') as f:
            return json.load(f)
    return None


def load_model_info():
    """Load model info."""
    info_path = os.path.join(OUTPUT_DIR, 'model_info.json')
    if os.path.exists(info_path):
        with open(info_path, 'r') as f:
            return json.load(f)
    return None


# ============================================================
# API ENDPOINTS
# ============================================================

@app.route('/api/predict', methods=['GET'])
def predict():
    """
    Prediksi kunjungan N bulan ke depan.
    Query params: months (default: 6)
    """
    months = request.args.get('months', 6, type=int)
    months = min(max(months, 1), 24)  # clamp 1-24
    
    # Load precomputed predictions
    predictions = load_predictions()
    if predictions:
        preds = predictions['predictions'][:months]
        return jsonify({
            'success': True,
            'data': preds,
            'model_info': predictions['model_info'],
            'months_requested': months,
            'months_returned': len(preds)
        })
    
    return jsonify({
        'success': False,
        'error': 'Model belum di-train. Jalankan pipeline SARIMA terlebih dahulu.'
    }), 404


@app.route('/api/historical', methods=['GET'])
def historical():
    """Data historis yang sudah diproses (monthly aggregated)."""
    data_path = os.path.join(PROCESSED_DIR, 'monthly_aggregated.csv')
    
    if not os.path.exists(data_path):
        return jsonify({
            'success': False,
            'error': 'Data belum diproses. Jalankan preprocessing terlebih dahulu.'
        }), 404
    
    df = pd.read_csv(data_path, index_col=0, parse_dates=True)
    
    records = []
    for idx, row in df.iterrows():
        records.append({
            'date': idx.strftime('%Y-%m-%d'),
            'year': int(row['Tahun']),
            'month': row['Bulan'],
            'month_num': int(row['Bulan_Num']),
            'ktm': int(row['KTM']),
            'glamping': int(row['Glamping']),
            'total_visitors': int(row['Total_Visitors']),
            'traffic_level': row.get('Traffic_Level', ''),
            'weeks_count': int(row['Weeks_Count'])
        })
    
    return jsonify({
        'success': True,
        'data': records,
        'total_months': len(records)
    })


@app.route('/api/model-info', methods=['GET'])
def model_info():
    """Informasi model SARIMA (akurasi, parameter)."""
    info = load_model_info()
    
    if info:
        return jsonify({
            'success': True,
            'data': info
        })
    
    return jsonify({
        'success': False,
        'error': 'Model info tidak tersedia.'
    }), 404


@app.route('/api/stats', methods=['GET'])
def stats():
    """Statistik ringkasan data kunjungan."""
    data_path = os.path.join(PROCESSED_DIR, 'monthly_aggregated.csv')
    
    if not os.path.exists(data_path):
        return jsonify({
            'success': False, 
            'error': 'Data belum diproses.'
        }), 404
    
    df = pd.read_csv(data_path, index_col=0, parse_dates=True)
    tv = df['Total_Visitors']
    
    # Load predictions for peak predicted
    predictions = load_predictions()
    pred_info = {}
    if predictions and predictions['predictions']:
        preds = predictions['predictions']
        peak_pred = max(preds, key=lambda x: x['predicted_visitors'])
        pred_info = {
            'peak_predicted_month': peak_pred['month'],
            'peak_predicted_year': peak_pred['year'],
            'peak_predicted_visitors': peak_pred['predicted_visitors'],
            'next_month_prediction': preds[0]['predicted_visitors'] if preds else 0,
            'forecast_months': len(preds)
        }
    
    result = {
        'total_months': len(df),
        'date_range': {
            'start': df.index[0].strftime('%Y-%m-%d'),
            'end': df.index[-1].strftime('%Y-%m-%d')
        },
        'total_visitors': int(tv.sum()),
        'avg_monthly_visitors': round(float(tv.mean()), 1),
        'std_dev': round(float(tv.std()), 1),
        'min_visitors': int(tv.min()),
        'min_month': tv.idxmin().strftime('%b %Y'),
        'max_visitors': int(tv.max()),
        'max_month': tv.idxmax().strftime('%b %Y'),
        'median': float(tv.median()),
        'predictions': pred_info
    }
    
    return jsonify({
        'success': True,
        'data': result
    })


@app.route('/api/retrain', methods=['POST'])
def retrain():
    """Retrain model SARIMA."""
    try:
        from preprocessing import run_preprocessing
        from sarima_model import run_sarima_pipeline
        
        # Re-run preprocessing
        run_preprocessing()
        
        # Re-run SARIMA pipeline
        months = request.json.get('forecast_months', 12) if request.json else 12
        fitted_model, forecast_df, metrics = run_sarima_pipeline(forecast_months=months)
        
        return jsonify({
            'success': True,
            'message': 'Model berhasil di-retrain.',
            'metrics': {
                'mae': round(metrics['mae'], 2),
                'rmse': round(metrics['rmse'], 2),
                'mape': round(metrics['mape'], 2),
                'quality': metrics['quality']
            }
        })
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/api/weekly', methods=['GET'])
def weekly_data():
    """Data mingguan yang sudah diproses."""
    data_path = os.path.join(PROCESSED_DIR, 'mapped_data.csv')
    
    if not os.path.exists(data_path):
        return jsonify({
            'success': False,
            'error': 'Data mingguan belum diproses.'
        }), 404
    
    df = pd.read_csv(data_path, index_col=0, parse_dates=True)
    
    records = []
    for idx, row in df.iterrows():
        records.append({
            'date': idx.strftime('%Y-%m-%d'),
            'year': int(row['Tahun']),
            'month': row['Bulan'],
            'week': int(row['Minggu']),
            'ktm': int(row['KTM']) if pd.notna(row['KTM']) else 0,
            'glamping': int(row['Glamping']) if pd.notna(row['Glamping']) else 0,
            'total_visitors': int(row['Total_Visitors']) if pd.notna(row['Total_Visitors']) else 0,
            'traffic_level': row.get('Traffic_Level', '')
        })
    
    return jsonify({
        'success': True,
        'data': records,
        'total_weeks': len(records)
    })


@app.route('/', methods=['GET'])
def index():
    """API root — health check."""
    return jsonify({
        'service': 'Bunihayu Tourism SARIMA Prediction API',
        'version': '1.0.0',
        'status': 'running',
        'endpoints': {
            'GET /api/predict?months=N': 'Prediksi N bulan ke depan',
            'GET /api/historical': 'Data historis bulanan',
            'GET /api/weekly': 'Data historis mingguan',
            'GET /api/model-info': 'Info model SARIMA',
            'GET /api/stats': 'Statistik ringkasan',
            'POST /api/retrain': 'Retrain model'
        }
    })


if __name__ == '__main__':
    print("\n" + "=" * 60)
    print("Bunihayu Tourism SARIMA API")
    print("=" * 60)
    print("Starting Flask server on http://localhost:5000")
    print("Endpoints available at /api/*\n")
    
    app.run(debug=True, host='0.0.0.0', port=5000)
