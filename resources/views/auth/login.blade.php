<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Logo Atelier -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-serif font-bold text-navy-900 mb-2">Atelier</h1>
        <p class="text-sm text-charcoal italic">L'atelier de votre production.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-navy-900 font-medium" />
            <x-text-input id="email" class="block mt-1 w-full border-navy-200 focus:border-gold-500 focus:ring-gold-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-navy-900 font-medium" />
            <x-text-input id="password" class="block mt-1 w-full border-navy-200 focus:border-gold-500 focus:ring-gold-500"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-navy-300 text-gold-500 shadow-sm focus:ring-gold-500" name="remember">
                <span class="ms-2 text-sm text-charcoal">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-navy-600 hover:text-navy-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="ms-3 inline-flex items-center px-6 py-2 bg-navy-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-navy-800 focus:bg-navy-800 active:bg-navy-950 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Log in') }}
            </button>
        </div>

        <!-- Link Register -->
        <div class="mt-6 text-center">
            <p class="text-sm text-charcoal">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-gold-600 hover:text-gold-700 font-medium">
                    Daftar di sini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>