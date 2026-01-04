<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login - QC Apps</title>

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
            overflow: hidden;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4e73df;
            position: relative;
            overflow: hidden;
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 2px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 32px 0 rgba(30, 60, 114, 0.3);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        @keyframes floatCard {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-icon {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 25px rgba(30, 60, 114, 0.2);
            padding: 15px;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .login-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: #4a90e2;
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

        /* Responsive Design */
        @media (max-width: 767.98px) {
            .login-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
                border-radius: 20px;
            }

            .login-icon {
                width: 100px;
                height: 100px;
                margin-bottom: 1rem;
                padding: 12px;
            }

            .login-icon i {
                font-size: 2rem;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .login-subtitle {
                font-size: 0.875rem;
            }

            .form-control-modern {
                padding: 0.875rem 1rem;
                font-size: 16px;
                /* Prevent zoom on iOS */
            }

            .btn-login {
                padding: 0.875rem;
            }
        }

        @media (max-width: 575.98px) {
            .login-card {
                padding: 1.75rem 1.25rem;
            }

            .login-icon {
                width: 90px;
                height: 90px;
                padding: 10px;
            }

            .login-icon i {
                font-size: 1.75rem;
            }

            .login-title {
                font-size: 1.35rem;
            }
        }
    </style>

</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <img src="{{ asset('master item/ipp.png') }}" alt="IPP Logo">
                </div>
                <h1 class="login-title">Quality Control</h1>
                <p class="login-subtitle">Silakan login untuk melanjutkan</p>
            </div>

            @if ($errors->any())
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
                    <input type="email" name="email" class="form-control-modern" id="exampleInputEmail"
                        placeholder="Email Address" required autofocus value="{{ old('email', $saved_email ?? '') }}">
                </div>

                <div class="form-group">
                    <div class="password-wrapper">
                        <input type="password" name="password" class="form-control-modern" id="exampleInputPassword"
                            placeholder="Password" required value="{{ $saved_password ?? '' }}">
                        <span toggle="#exampleInputPassword" class="fas fa-eye toggle-password"></span>
                    </div>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="remember" id="customCheck" {{ (old('remember') || ($is_remembered ?? false)) ? 'checked' : '' }}>
                    <label for="customCheck">Remember Me</label>
                </div>

                <button type="submit" class="btn-login">
                    Login
                </button>
            </form>
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