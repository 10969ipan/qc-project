<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>MASUK - QC APPS</title>
    <link rel="icon" href="{{ asset('master item/ipp.png') }}" type="image/png">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet" type="text/css">
    <link href="{{ asset('fonts/inter.css') }}" rel="stylesheet">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-responsive.css') }}" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            overflow-x: hidden;
            overflow-y: auto;
            min-height: 100vh;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            background-image: url('{{ asset("master item/bg_pattern.png") }}');
            background-size: 400px;
            background-repeat: repeat;
            position: relative;
            padding: 2rem 1rem;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            z-index: 0;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 1);
            border-radius: 28px;
            border: 1px solid #edf2f7;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 950px;
            position: relative;
            z-index: 1;
            margin: auto;
            display: flex;
            overflow: hidden;
            flex-direction: row;
        }

        .login-side-form {
            flex: 1;
            padding: 4.5rem 3.5rem;
            max-width: 460px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .login-side-image {
            flex: 1.3;
            background-image: url('{{ asset("master item/indoplat.jpg") }}');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-end;
            padding: 4rem 3.5rem;
            color: white;
            text-align: left;
        }

        .login-side-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(145deg, rgba(30, 60, 114, 0.85) 0%, rgba(42, 82, 152, 0.7) 100%);
            z-index: 1;
        }

        .banner-content {
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .banner-title {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.1;
        }

        .banner-divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }

        .banner-divider::after {
            content: '';
            height: 4px;
            background: #d1d3e2;
            flex-grow: 1;
            border-radius: 2px;
        }

        .banner-subtitle {
            font-size: 1.15rem;
            opacity: 0.95;
            font-weight: 300;
            letter-spacing: 1px;
            line-height: 1.6;
        }

        .login-header {
            text-align: left;
            margin-bottom: 2.5rem;
        }

        .login-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.75rem;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            font-size: 1rem;
            color: #718096;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-control-modern {
            width: 100%;
            padding: 1rem 1.25rem;
            background: rgba(255, 255, 255, 1);
            border: 2px solid #e3f2fd;
            border-radius: 12px;
            font-size: 0.95rem;
            color: #2d3748;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-control-modern::placeholder {
            color: #a0aec0;
        }

        .form-control-modern:focus {
            outline: none;
            background: rgba(255, 255, 255, 1);
            border-color: #2a5298;
            box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.15);
            transform: translateY(-2px);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #718096;
            transition: color 0.3s ease;
            z-index: 10;
        }

        .toggle-password:hover {
            color: #2a5298;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 0.5rem;
            cursor: pointer;
            accent-color: #2a5298;
        }

        .checkbox-wrapper label {
            color: #4a90e2;
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
            font-family: 'Inter', sans-serif;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.5);
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-modern {
            background: rgba(239, 68, 68, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #c53030;
        }

        .alert-modern ul {
            margin: 0;
            padding-left: 1.25rem;
            list-style: none;
        }

        .alert-modern li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .alert-modern li:before {
            content: '⚠';
            position: absolute;
            left: 0;
            font-size: 1rem;
        }

        .alert-modern li:last-child {
            margin-bottom: 0;
        }

        /* Responsive Design Upgrades */
        @media (max-width: 991.98px) {
            .login-card {
                flex-direction: column;
                max-width: 480px;
            }

            .login-side-image {
                display: none;
            }

            .login-side-form {
                max-width: 100%;
                padding: 3rem 2rem;
            }
        }

        @media (max-width: 767.98px) {
            .login-container {
                padding: 1.5rem 1rem;
            }

            .login-card {
                border-radius: 20px;
            }

            .login-icon {
                width: 100px;
                height: 100px;
                margin-bottom: 1.25rem;
                padding: 12px;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .login-subtitle {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 1.75rem 1.25rem;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .login-icon {
                width: 85px;
                height: 85px;
            }

            .login-title {
                font-size: 1.35rem;
            }

            .form-control-modern {
                padding: 0.85rem 1rem;
                font-size: 16px;
                /* iOS zoom prevention */
            }
        }

        /* Landscape orientation handling for mobile */
        @media (max-height: 600px) and (orientation: landscape) {
            .login-container {
                padding: 1rem;
                align-items: flex-start;
            }

            .login-card {
                padding: 1.5rem;
            }

            .login-header {
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                text-align: left;
            }

            .login-icon {
                width: 60px;
                height: 60px;
                margin: 0 1.5rem 0 0;
            }

            .login-title {
                font-size: 1.25rem;
                margin-bottom: 0;
            }

            .form-group {
                margin-bottom: 1rem;
            }
        }
    </style>

</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-side-form">
                <div class="login-header">
                    <h1 class="login-title">Masuk</h1>
                    <p class="login-subtitle">Masukkan akun Anda untuk melanjutkan</p>
                </div>

                @if (isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag && $errors->any())
                    <div class="alert-modern">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.process') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold text-gray-600 mb-1">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control-modern" id="exampleInputEmail"
                            placeholder="nama@email.com" required autofocus
                            value="{{ old('email', $saved_email ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-gray-600 mb-1">PASSWORD</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" class="form-control-modern" id="exampleInputPassword"
                                placeholder="••••••••" required value="{{ $saved_password ?? '' }}">
                            <span toggle="#exampleInputPassword" class="fas fa-eye toggle-password"></span>
                        </div>
                    </div>


                    <button type="submit" class="btn-login">
                        Masuk
                    </button>
                </form>
            </div>
            <div class="login-side-image">
                <img src="{{ asset('master item/logo-ipp.png') }}" alt="Logo IPP"
                    style="position: absolute; top: 20px; right: 20px; width: 100px; z-index: 30;">
                <div class="banner-content">
                    <h2 class="banner-title">QC APPS<br></h2>
                    <div class="banner-divider"></div>
                    <p class="banner-subtitle">
                        INTEGRATED DIGITAL QUALITY MANAGEMENT &<br>
                        REAL-TIME ANALYTICS
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/jquery/jquery.min.js') }}"></script>
    <script
        src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/js/sb-admin-2.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.querySelector('.toggle-password');
            const password = document.querySelector('#exampleInputPassword');

            if (togglePassword && password) {
                togglePassword.addEventListener('click', function (e) {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>

</html>