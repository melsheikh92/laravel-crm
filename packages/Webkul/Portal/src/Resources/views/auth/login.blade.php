@extends('portal::layouts.master')

@section('title', 'Login')

@section('content')
    <div class="flex items-center justify-center min-h-screen">
        <div class="card w-full" style="max-width: 400px;">
            <div class="text-center mb-4">
                <h1 style="font-size: 1.5rem; font-weight: 700;">Welcome Back</h1>
                <p style="color: var(--text-secondary);">Sign in to access your dashboard</p>
            </div>

            <form method="POST" action="{{ route('portal.login.store') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                        value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" required>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="flex items-center justify-between mb-4">
                    <label class="flex items-center">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm" style="margin-left: 0.5rem;">Remember Me</span>
                    </label>

                    {{-- <a class="text-sm btn-link" href="#">
                        Forgot Password?
                    </a> --}}
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Sign In
                </button>
            </form>
        </div>
    </div>
@endsection