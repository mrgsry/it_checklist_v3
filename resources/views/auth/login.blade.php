@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center px-3 py-5"
     style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);">

    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 w-100"
         style="max-width: 420px; background: #fff;">

        <div class="text-center mb-4">
            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width: 60px; height: 60px; box-shadow: 0 8px 20px rgba(30,58,95,0.25);">
                <i class="fas fa-check-double text-white fs-4"></i>
            </div>
            <h4 class="fw-bold mb-1">IT Checklist App</h4>
            <p class="text-muted mb-0">Masuk untuk melanjutkan</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email"
                           class="form-control border-start-0 ps-0"
                           placeholder="email@example.com"
                           value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="password"
                           id="password"
                           class="form-control border-start-0 border-end-0 ps-0"
                           placeholder="••••••••" required>
                    <button class="input-group-text bg-light border-start-0" type="button" id="togglePassword">
                        <i class="fas fa-eye text-muted" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-body" for="remember">Ingat Saya</label>
                </div>
                {{-- Uncomment jika ada route lupa password
                <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa password?</a>
                --}}
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fw-semibold">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk
            </button>
        </form>

        
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn?.addEventListener('click', function () {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleIcon.classList.toggle('fa-eye');
        toggleIcon.classList.toggle('fa-eye-slash');
    });
</script>
@endsection