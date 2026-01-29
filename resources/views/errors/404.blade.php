<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found | Warehouse POS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #3deb03 0%, #f1e376 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background circles */
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            animation: float 20s infinite ease-in-out;
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            right: -100px;
            animation-delay: 5s;
        }

        .circle-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) scale(1);
            }
            50% {
                transform: translateY(-30px) scale(1.1);
            }
        }

        .error-container {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 404 Illustration */
        .error-illustration {
            position: relative;
            margin-bottom: 30px;
        }

        .error-number {
            font-size: 140px;
            font-weight: 900;
            background: linear-gradient(135deg, #3deb03 0%, #f1e376 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            position: relative;
            display: inline-block;
            animation: glitch 3s infinite;
        }

        @keyframes glitch {
            0%, 90%, 100% {
                transform: translate(0);
            }
            92% {
                transform: translate(-2px, 2px);
            }
            94% {
                transform: translate(2px, -2px);
            }
            96% {
                transform: translate(-2px, -2px);
            }
            98% {
                transform: translate(2px, 2px);
            }
        }

        .floating-icon {
            position: absolute;
            font-size: 40px;
            opacity: 0.3;
            color: #3deb03;
            animation: floatIcon 4s infinite ease-in-out;
        }

        .icon-1 {
            top: 20px;
            left: 20px;
            animation-delay: 0s;
        }

        .icon-2 {
            top: 30px;
            right: 30px;
            animation-delay: 1s;
        }

        .icon-3 {
            bottom: 20px;
            left: 40px;
            animation-delay: 2s;
        }

        @keyframes floatIcon {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }

        .error-title {
            font-size: 32px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 15px;
        }

        .error-description {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3deb03 0%, #f1e376 100%);
            color: #1f2937;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(61, 235, 3, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(61, 235, 3, 0.6);
            color: #1f2937;
        }

        .btn-secondary {
            background: white;
            color: #3deb03;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            border: 2px solid #3deb03;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #3deb03 0%, #f1e376 100%);
            color: #1f2937;
            border-color: #3deb03;
            transform: translateY(-2px);
        }

        .help-links {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .help-links h6 {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .help-links-grid {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .help-link {
            color: #3deb03;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .help-link:hover {
            color: #2bb802;
            transform: translateX(3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .error-card {
                padding: 40px 25px;
            }

            .error-number {
                font-size: 100px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-description {
                font-size: 14px;
            }

            .btn-group {
                flex-direction: column;
                width: 100%;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            .floating-icon {
                font-size: 30px;
            }
        }

        /* Logo */
        .logo-section {
            margin-bottom: 30px;
        }

        .logo-circle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3deb03 0%, #f1e376 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 16px rgba(61, 235, 3, 0.3);
        }

        .logo-circle i {
            font-size: 30px;
            color: white;
        }

        .brand-name {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        /* Additional decorative elements */
        .pattern-dots {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0.1;
            background-image: radial-gradient(circle, #1f2937 1px, transparent 1px);
            background-size: 30px 30px;
            pointer-events: none;
        }
    </style>
</head>
<body>

    {{-- Pattern Overlay --}}
    <div class="pattern-dots"></div>

    {{-- Animated Background --}}
    <div class="bg-circle circle-1"></div>
    <div class="bg-circle circle-2"></div>
    <div class="bg-circle circle-3"></div>

    <div class="error-container">
        <div class="error-card">
            
            {{-- Logo --}}
            <div class="logo-section">
                <div class="logo-circle">
                    <i class="mdi mdi-warehouse"></i>
                </div>
                <h5 class="brand-name">Warehouse POS</h5>
            </div>

            {{-- Error Illustration --}}
            <div class="error-illustration">
                <div class="floating-icon icon-1">
                    <i class="mdi mdi-map-marker-question-outline"></i>
                </div>
                <div class="floating-icon icon-2">
                    <i class="mdi mdi-file-question-outline"></i>
                </div>
                <div class="floating-icon icon-3">
                    <i class="mdi mdi-compass-off-outline"></i>
                </div>
                
                <div class="error-number">404</div>
            </div>

            {{-- Error Message --}}
            <h1 class="error-title">Oops! Page Not Found</h1>
            <p class="error-description">
                We can't seem to find the page you're looking for. The page might have been moved, deleted, or never existed in the first place.
            </p>

            {{-- Action Buttons --}}
            <div class="btn-group">
                <a href="{{ route('dashboard') }}" class="btn-primary">
                    <i class="mdi mdi-home-outline"></i>
                    Back to Dashboard
                </a>
                <a href="javascript:history.back()" class="btn-secondary">
                    <i class="mdi mdi-arrow-left"></i>
                    Go Back
                </a>
            </div>

            {{-- Help Links --}}
            <div class="help-links">
                <h6>Quick Links</h6>
                <div class="help-links-grid">
                    <a href="{{ route('dashboard') }}" class="help-link">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        Dashboard
                    </a>
                    <a href="#" class="help-link">
                        <i class="mdi mdi-warehouse"></i>
                        Warehouse
                    </a>
                    <a href="#" class="help-link">
                        <i class="mdi mdi-package-variant"></i>
                        Products
                    </a>
                    <a href="#" class="help-link">
                        <i class="mdi mdi-help-circle-outline"></i>
                        Help Center
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Add some interactive animation on mouse move
        document.addEventListener('mousemove', (e) => {
            const circles = document.querySelectorAll('.bg-circle');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            circles.forEach((circle, index) => {
                const speed = (index + 1) * 20;
                const xMove = (x - 0.5) * speed;
                const yMove = (y - 0.5) * speed;
                circle.style.transform = `translate(${xMove}px, ${yMove}px)`;
            });
        });

        // Add subtle floating animation to the card
        const card = document.querySelector('.error-card');
        let cardFloat = 0;
        setInterval(() => {
            cardFloat += 0.05;
            card.style.transform = `translateY(${Math.sin(cardFloat) * 5}px)`;
        }, 50);
    </script>

</body>
