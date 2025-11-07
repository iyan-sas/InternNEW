<?php

use App\Enums\UserRole;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], (bool) $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // ✅ Successful auth
        RateLimiter::clear($this->throttleKey());
        request()->session()->regenerate();

        // 🔒 Block users until approved
        if (! auth()->user()->is_approved) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Your account is pending approval. Please wait for admin approval.',
            ]);
        }

        // ✅ Admin gets a one-time flash to show the welcome banner inside the blue header
        if (auth()->user()->role === UserRole::Admin) {
            return redirect()
                ->route('dashboard')
                ->with('welcome_admin', true); // <<— use this in your dashboard Blade
        }

        // ✅ Everyone else: hard server redirect (avoids white screen)
        return redirect()->route('dashboard');
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
};
?>

<!-- === VIEW === -->
<div class="min-h-screen flex items-center justify-center bg-cover bg-center"
     style="background-image: url('/images/dhvsu-gate.jpg')">

    <div class="p-8 rounded-lg shadow-xl w-96 text-gray-900 border border-blue-300"
         style="background-color: rgba(191, 219, 254, 0.65); backdrop-filter: blur(8px);">

        <div class="flex justify-center mb-4">
            <img src="{{ asset('career_services.png') }}" alt="Career Services Logo" class="h-16">
        </div>

        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form wire:submit="login" class="flex flex-col gap-6">
            <flux:input
                wire:model.defer="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />
            @error('email') <div class="text-red-600 text-sm -mt-4">{{ $message }}</div> @enderror

            <div class="relative">
                <flux:input
                    wire:model.defer="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="absolute end-0 top-0 text-sm text-black hover:underline">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
            @error('password') <div class="text-red-600 text-sm -mt-4">{{ $message }}</div> @enderror

            <flux:checkbox wire:model="remember" :label="__('Remember me')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-700 mt-4">
                {{ __('Don\'t have an account?') }}
                <a href="{{ route('register') }}" class="text-black hover:underline">Sign up</a>
            </div>
        @endif
    </div>
</div>
