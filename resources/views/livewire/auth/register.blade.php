<?php

use App\Models\User;
use App\Enums\UserRole;
use App\Mail\NewSignupForApproval;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $admin_code = '';   // ✅ secret code

    public function register(): void
    {
        $validated = $this->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','string','lowercase','email','max:255','unique:'.User::class],
            'password'   => ['required','confirmed', Password::defaults()],
            'admin_code' => ['required','string'],
        ]);

        // (Optional) restrict to a domain
        // if (! str_ends_with(strtolower($validated['email']), '@pampangastateu.edu.ph')) {
        //     $this->addError('email', 'Institutional email only.');
        //     return;
        // }

        // ✅ Validate secret code from .env
        if (! hash_equals(env('ADMIN_REG_CODE',''), $validated['admin_code'])) {
            $this->addError('admin_code', 'Invalid admin registration code.');
            return;
        }

        // ✅ Create as Admin but set pending approval
        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role'        => UserRole::Admin,   // enum case in your app
            'is_approved' => false,
        ]);

        event(new Registered($user));

        // ✅ Notify creators (Mailtrap/Gmail based on .env)
        $recipients = collect(explode(',', env('APPROVAL_RECIPIENTS','')))
            ->map(fn($e) => trim($e))
            ->filter()
            ->all();

        if (!empty($recipients)) {
            Mail::to($recipients)->send(new NewSignupForApproval($user));
        }

        // ✅ Do not log in yet — force a full reload so the login page renders immediately
        session()->flash('status', 'Your admin request is pending approval by the creators.');
        $this->redirect(route('login'), navigate: false); // << full page reload
    }
};
?>

<!-- === VIEW === -->
<div class="min-h-screen flex items-center justify-center bg-cover bg-center"
     style="background-image: url('/images/dhvsu-gate.jpg')">

    <div class="p-8 rounded-lg shadow-xl w-96 text-gray-900 border border-blue-300"
         style="background-color: rgba(191, 219, 254, 0.65); backdrop-filter: blur(8px);">
        
        <!-- Logo -->
        <div class="flex justify-center mb-4">
            <img src="{{ asset('career_services.png') }}" alt="Career Services Logo" class="h-16">
        </div>

        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form wire:submit="register" class="flex flex-col gap-6">
            <!-- Name -->
            <flux:input
                wire:model.defer="name"
                :label="__('Name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />
            @error('name') <div class="text-red-600 text-sm -mt-4">{{ $message }}</div> @enderror

            <!-- Email Address -->
            <flux:input
                wire:model.defer="email"
                :label="__('Email address')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />
            @error('email') <div class="text-red-600 text-sm -mt-4">{{ $message }}</div> @enderror

            <!-- Password -->
            <flux:input
                wire:model.defer="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />
            @error('password') <div class="text-red-600 text-sm -mt-4">{{ $message }}</div> @enderror

            <!-- Confirm Password -->
            <flux:input
                wire:model.defer="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            <!-- ✅ Admin Registration Code (required) -->
            <flux:input
                wire:model.defer="admin_code"
                :label="__('Admin registration code')"
                type="text"
                required
                :placeholder="__('Enter the code from creators')"
            />
            @error('admin_code') <div class="text-red-600 text-sm -mt-4">{{ $message }}</div> @enderror

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 mt-4">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="text-black hover:underline">Log in</a>
        </div>
    </div>
</div>
