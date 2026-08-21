<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    @include('partials.head', ['title' => 'Sign In - ' . config('admin.name', 'Grocery Admin')])
</head>
<body class="h-full font-sans antialiased text-slate-900 bg-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Subtle Background Ambient Glow -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
        <!-- Brand Logo & Title -->
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white shadow-xl shadow-emerald-900/40 mb-4">
            <i data-lucide="shopping-cart" class="w-7 h-7"></i>
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-white">
            {{ config('admin.name', 'Grocery Admin') }}
        </h2>
        <p class="mt-1 text-xs text-slate-400">
            Sign in to access your supermarket management portal
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
        <div class="bg-white py-8 px-6 shadow-2xl rounded-2xl sm:px-10 border border-slate-100/10">
            <!-- Flash & Error alerts -->
            @include('partials.flash')

            <form method="POST" action="{{ route('admin.login.submit') }}" id="login-form" class="space-y-5">
                @csrf

                <!-- Email Address -->
                <x-form.input
                    name="email"
                    type="email"
                    label="Email Address"
                    placeholder="admin@grocery.local"
                    icon="mail"
                    :value="old('email', 'admin@grocery.local')"
                    required
                    autofocus
                />

                <!-- Password -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-semibold text-slate-700">
                            Password <span class="text-rose-500">*</span>
                        </label>
                    </div>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            value="password"
                            required
                            placeholder="••••••••"
                            class="w-full rounded-lg text-sm bg-white border border-slate-300 text-slate-900 pl-10 pr-10 py-2.5 transition-colors focus:outline-hidden focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                        />
                        <button
                            type="button"
                            id="toggle-password-btn"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-rose-600 flex items-center gap-1 mt-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <x-form.checkbox
                        name="remember"
                        label="Remember my session"
                        :checked="true"
                    />
                </div>

                <!-- Submit Button -->
                <div>
                    <x-admin.button
                        type="submit"
                        id="submit-login-btn"
                        variant="primary"
                        class="w-full py-3"
                    >
                        Sign in to Dashboard
                    </x-admin.button>
                </div>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 text-center mb-3">
                    Quick Demo Credentials
                </p>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        type="button"
                        onclick="fillCredentials('admin@grocery.local', 'password')"
                        class="px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 text-[11px] font-medium text-slate-700 hover:text-emerald-700 transition-colors text-center"
                    >
                        Admin
                    </button>
                    <button
                        type="button"
                        onclick="fillCredentials('manager@grocery.local', 'password')"
                        class="px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 text-[11px] font-medium text-slate-700 hover:text-emerald-700 transition-colors text-center"
                    >
                        Manager
                    </button>
                    <button
                        type="button"
                        onclick="fillCredentials('staff@grocery.local', 'password')"
                        class="px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 text-[11px] font-medium text-slate-700 hover:text-emerald-700 transition-colors text-center"
                    >
                        Staff
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ config('admin.name', 'Grocery Admin') }}. All rights reserved.
        </p>
    </div>

    @include('partials.scripts')

    <script>
        function fillCredentials(email, password) {
            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="password"]').value = password;
            Admin.toast({ type: 'info', message: 'Filled ' + email });
        }

        // Toggle password visibility
        document.getElementById('toggle-password-btn')?.addEventListener('click', function () {
            const pwdInput = document.getElementById('password');
            const icon = this.querySelector('[data-lucide]');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwdInput.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            Admin.refreshIcons();
        });

        // Button loading on submit
        $('#login-form').on('submit', function () {
            Admin.btnLoading('#submit-login-btn', true, 'Signing in...');
        });
    </script>
</body>
</html>
