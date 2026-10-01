<x-guest-layout>
    <!-- Logo Atelier -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-serif font-bold text-navy-900 mb-2">Atelier</h1>
        <p class="text-sm text-charcoal italic">L'atelier de votre production.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-navy-900 font-medium" />
            <x-text-input id="name" class="block mt-1 w-full border-navy-200 focus:border-gold-500 focus:ring-gold-500" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="text-navy-900 font-medium" />
            <x-text-input id="email" class="block mt-1 w-full border-navy-200 focus:border-gold-500 focus:ring-gold-500" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-navy-900 font-medium" />
            <x-text-input id="password" class="block mt-1 w-full border-navy-200 focus:border-gold-500 focus:ring-gold-500"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-navy-900 font-medium" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full border-navy-200 focus:border-gold-500 focus:ring-gold-500"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-navy-600 hover:text-navy-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" class="ms-3 inline-flex items-center px-6 py-2 bg-navy-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-navy-800 focus:bg-navy-800 active:bg-navy-950 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</x-guest-layout>