<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>No Internet Connection | Warehouse POS</title>
    
    {{-- CSS Libraries (Bootstrap + Icons) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .offline-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            max-width: 480px;
            width: 100%;
            background: white;
            text-align: center;
            padding: 40px 30px;
        }
        .icon-circle {
            width: 100px;
            height: 100px;
            background: #ffebeb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            position: relative;
        }
        .icon-circle i {
            font-size: 48px;
            color: #ef4444;
        }
        .icon-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 4px solid rgba(239, 68, 68, 0.2);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }
        .btn-retry {
            background: #111827;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            transition: all 0.2s;
        }
        .btn-retry:hover {
            background: #000;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .steps-box {
            background: #f9fafb;
            border-radius: 12px;
            padding: 15px;
            margin: 25px 0;
            text-align: left;
        }
        .steps-box li {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .steps-box li i {
            color: #10b981;
            margin-right: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>

    <div class="offline-card">
        {{-- Icon --}}
        <div class="icon-circle">
            <div class="icon-pulse"></div>
            <i class="mdi mdi-wifi-off"></i>
        </div>

        {{-- Heading --}}
        <h3 class="fw-bold text-dark mb-2">Connection Lost</h3>
        <p class="text-muted mb-0">Oops! It looks like you're offline. Please check your internet connection and try again.</p>

        {{-- Helpful Steps --}}
        <div class="steps-box">
            <ul class="list-unstyled mb-0">
                <li><i class="mdi mdi-check-circle-outline"></i> Check your WiFi or data cables</li>
                <li><i class="mdi mdi-check-circle-outline"></i> Ensure router is powered on</li>
                <li class="mb-0"><i class="mdi mdi-check-circle-outline"></i> Reconnect to your network</li>
            </ul>
        </div>

        {{-- Action Buttons --}}
        <div class="d-grid gap-2">
            <button onclick="checkConnection()" class="btn btn-retry" id="retryBtn">
                <i class="mdi mdi-refresh me-2"></i> Try Reconnecting
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-link text-muted text-decoration-none">
                Go Back Previous Page
            </a>
        </div>
    </div>

    <script>
        function checkConnection() {
            var btn = document.getElementById('retryBtn');
            var originalText = btn.innerHTML;
            
            // Loading State
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Checking...';
            btn.disabled = true;

            // Simple ping to check connection
            fetch('{{ route('login') }}', { method: 'HEAD', cache: 'no-store' })
                .then(function() {
                    // Success: Redirect back or reload
                    window.location.href = "{{ url()->previous() }}";
                })
                .catch(function() {
                    // Still Offline
                    setTimeout(function() {
                        btn.innerHTML = '<i class="mdi mdi-alert-circle-outline me-2"></i> Still Offline';
                        btn.classList.add('btn-danger');
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                            btn.classList.remove('btn-danger');
                        }, 2000);
                    }, 1000);
                });
        }

        // Auto-check every 5 seconds
        setInterval(() => {
            if (navigator.onLine) {
                checkConnection();
            }
        }, 5000);
    </script>
</body>
</html>