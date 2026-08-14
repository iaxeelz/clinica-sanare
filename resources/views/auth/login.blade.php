<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Mensajes de error personalizados -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach ($errors->all() as $error)
                @if (str_contains($error, 'credentials'))
                    <i class="fas fa-exclamation-circle me-2"></i> Las credenciales no coinciden con nuestros registros.
                @else
                    {{ $error }}
                @endif
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-envelope"></i>
                </span>
                <input id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required 
                    autofocus 
                    autocomplete="username"
                    placeholder="tu@email.com"
                    class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle me-1"></i> Las credenciales no coinciden con nuestros registros.
                    </div>
                @enderror
            </div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input id="password" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle me-1"></i> Las credenciales no coinciden con nuestros registros.
                    </div>
                @enderror
            </div>
        </div>

        <!-- Recordarme -->
        <div class="remember-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                <label class="form-check-label" for="remember_me">
                    Recordarme
                </label>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-signin">
            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
        </button>

        <!-- Footer -->
        <div class="login-footer">
            <p>
                Al hacer clic en "Iniciar Sesión" aceptas los 
                <a href="#">Términos de Servicio</a> | <a href="#">Política de Privacidad</a>
            </p>
            <p style="margin-top: 10px;">&copy; {{ date('Y') }} Clínica Sanare</p>
        </div>
    </form>
</x-guest-layout>