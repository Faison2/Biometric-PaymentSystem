<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top, #0f1c2e 0%, #000 100%);
            color: white;
            height: 100vh;
            overflow: hidden;
        }

        header {
            padding: 2rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-weight: 600;
            font-size: 1.25rem;
        }

        nav a {
            color: #ccc;
            margin-left: 2rem;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: white;
        }

        .hero {
            text-align: center;
            margin-top: 4rem;
            padding: 0 1rem;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(90deg, #ff00cc, #3333ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero h2 {
            font-size: 2rem;
            margin-top: 0.5rem;
            font-weight: 600;
            color: white;
        }

        .hero p {
            margin: 1rem auto 2rem;
            max-width: 600px;
            font-size: 1rem;
            color: #bbb;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: 1px solid #fff;
            border-radius: 25px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            background: transparent;
        }

        .btn:hover {
            background: white;
            color: #000;
        }

        .illustration {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            max-width: 700px;
            width: 100%;
            z-index: -1;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.2rem;
            }

            .hero h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">BioMetric-Auth</div>
        <nav>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/home') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}">Register</a>
                    @endif
                @endauth
            @endif
        </nav>
    </header>

    <div class="hero">
        <h1>Two Factor Authentication .</h1>
        <h2>Maximum Security.</h2>
        <p>Our technology delivers 120K TPS blockchain, with amaizing speed, no more fraud provide your face and voice to pay </p>
        <div class="buttons">
            <a href="#" class="btn">Get Started</a>
            <a href="#" class="btn">Ecosystem</a>
        </div>
    </div>

    <!-- <img src="{{ asset('images/illustration.jpg') }}" alt="Illustration" class="illustration"> -->
</body>
</html>
