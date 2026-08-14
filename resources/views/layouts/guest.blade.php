<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Bootstrap 5 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
        <!-- Font Awesome 6 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                min-height: 100vh;
                background: url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=1920&q=80') no-repeat center center fixed;
                background-size: cover;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Capa oscura sobre la imagen */
            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: 0;
            }

            .login-container {
                position: relative;
                z-index: 1;
                width: 100%;
                max-width: 400px;
                padding: 20px;
            }

            /* Logo - Solo texto */
            .login-logo {
                text-align: center;
                margin-bottom: 35px;
            }

            .login-logo h1 {
                font-size: 42px;
                font-weight: 300;
                color: white;
                margin: 0;
                letter-spacing: 4px;
                text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
            }

            .login-logo p {
                color: rgba(255, 255, 255, 0.6);
                font-size: 14px;
                margin: 8px 0 0;
                letter-spacing: 1px;
            }

            /* Formulario - Sin card, transparente */
            .form-group {
                margin-bottom: 18px;
            }

            .form-group label {
                display: block;
                font-size: 13px;
                font-weight: 500;
                color: rgba(255, 255, 255, 0.8);
                margin-bottom: 6px;
                letter-spacing: 0.5px;
            }

            .input-group {
                display: flex;
                align-items: center;
                border-bottom: 2px solid rgba(255, 255, 255, 0.2);
                transition: all 0.3s;
                background: transparent;
            }

            .input-group:focus-within {
                border-bottom-color: #ffffff;
            }

            .input-group-text {
                background: transparent;
                border: none;
                padding: 0 0 0 4px;
                color: rgba(255, 255, 255, 0.4);
                font-size: 15px;
            }

            .input-group .form-control {
                border: none;
                padding: 12px 14px;
                font-size: 15px;
                background: transparent;
                border-radius: 0;
                color: white;
            }

            .input-group .form-control:focus {
                box-shadow: none;
                background: transparent;
            }

            .input-group .form-control::placeholder {
                color: rgba(255, 255, 255, 0.3);
            }

            /* ============================================
               ESTILOS PARA ERRORES - CORREGIDO
            ============================================ */
            .input-group .form-control.is-invalid {
                border-bottom: 2px solid #ff6b6b;
            }

            .input-group .form-control.is-invalid:focus {
                border-bottom-color: #ff6b6b;
            }

            .invalid-feedback {
                display: block;
                font-size: 12px;
                color: #ff6b6b;
                margin-top: 6px;
                padding-left: 4px;
            }

            .invalid-feedback i {
                margin-right: 6px;
            }

            /* ============================================
               QUITAR FONDO BLANCO EN AUTOFILL
            ============================================ */
            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus,
            input:-webkit-autofill:active {
                -webkit-box-shadow: 0 0 0 30px transparent inset !important;
                -webkit-text-fill-color: white !important;
                background-color: transparent !important;
                background: transparent !important;
                transition: background-color 5000s ease-in-out 0s;
            }

            input:-moz-autofill {
                background-color: transparent !important;
                color: white !important;
            }

            input:autofill {
                background-color: transparent !important;
                color: white !important;
            }

            input:-webkit-autofill::first-line {
                color: white !important;
            }

            .input-group .form-control:-webkit-autofill {
                -webkit-text-fill-color: white !important;
            }

            /* Recordarme */
            .remember-group {
                display: flex;
                justify-content: flex-start;
                align-items: center;
                margin: 22px 0 28px;
            }

            .form-check {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .form-check-input {
                width: 16px;
                height: 16px;
                border: 2px solid rgba(255, 255, 255, 0.3);
                border-radius: 4px;
                cursor: pointer;
                background: transparent;
            }

            .form-check-input:checked {
                background-color: white;
                border-color: white;
            }

            .form-check-input:focus {
                box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
            }

            .form-check-label {
                font-size: 13px;
                color: rgba(255, 255, 255, 0.7);
                cursor: pointer;
            }

            /* Botón */
            .btn-signin {
                width: 100%;
                padding: 14px;
                background: white;
                color: #1a1a2e;
                border: none;
                border-radius: 8px;
                font-size: 15px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                letter-spacing: 1px;
            }

            .btn-signin:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            }

            .btn-signin:active {
                transform: translateY(0);
            }

            /* ============================================
               ALERTAS - CORREGIDO
            ============================================ */
            .alert {
                border-radius: 8px;
                font-size: 13px;
                padding: 12px 16px;
                margin-bottom: 20px;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: white;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .alert i {
                font-size: 16px;
            }

            .alert-success {
                background: rgba(40, 167, 69, 0.2);
                border-color: rgba(40, 167, 69, 0.3);
            }

            .alert-success i {
                color: #28a745;
            }

            .alert-danger {
                background: rgba(220, 53, 69, 0.2);
                border-color: rgba(220, 53, 69, 0.3);
            }

            .alert-danger i {
                color: #ff6b6b;
            }

            .alert .btn-close {
                filter: brightness(0) invert(1);
                font-size: 12px;
                margin-left: auto;
                padding: 5px;
            }

            /* Footer */
            .login-footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
            }

            .login-footer p {
                font-size: 12px;
                color: rgba(255, 255, 255, 0.3);
                margin: 0;
                letter-spacing: 0.5px;
            }

            .login-footer a {
                color: rgba(255, 255, 255, 0.5);
                text-decoration: none;
                transition: color 0.3s;
            }

            .login-footer a:hover {
                color: white;
            }

            /* Responsive */
            @media (max-width: 480px) {
                .login-container {
                    padding: 15px;
                }

                .login-logo h1 {
                    font-size: 32px;
                }

                .btn-signin {
                    padding: 12px;
                    font-size: 14px;
                }

                .alert {
                    font-size: 12px;
                    padding: 10px 12px;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <!-- Logo Sanare - Solo texto -->
            <div class="login-logo">
                <h1>sanare</h1>
                <p>Inicia sesión en tu cuenta</p>
            </div>

            <!-- Contenido del formulario -->
            {{ $slot }}
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>