<x-guest-layout>
    <div class="w-full h-full bg-cover bg-center min-h-screen min-w-screen"
        style="background-image: url('{{ asset('images/bg-1.png') }}');">
        {{-- login form --}}
        <div class="w-full h-full flex flex-col items-end justify-center min-h-screen p-4 md:p-12">
            <div class="flex items-center justify-center w-full bg-white shadow rounded-lg h-full max-w-md mx-auto">
                <form method="POST" action="{{ route('login') }}" class="w-full p-6 h-full flex flex-col gap-4 items-center justify-center">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    @csrf

                    <!-- Email Address -->
                    <div class="w-full">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                            :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="w-full relative">
                        <div class="inline-flex justify-between w-full">
                        <x-input-label for="password" :value="__('Password')" />
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                        </div>

                        <div class="relative">
                            <x-text-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required
                                autocomplete="current-password" />
                            <button type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-900 mt-1"
                                onclick="togglePassword()">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="block w-full">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox"
                                class="rounded-sm border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500"
                                name="remember">
                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-center w-full">
                        <x-primary-button class="bg-green-600 hover:bg-green-700 w-full justify-center">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>

                    <!-- Don't have an account -->
                    <div class="w-full text-center">
                        <p class="text-sm text-gray-600">
                            {{ __("Don't have an account?") }}
                            @if (Route::has('register'))
                                <a class="underline text-green-600 hover:text-green-700 font-medium" href="{{ route('register') }}">
                                    {{ __('Sign up') }}
                                </a>
                            @endif
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }
    </script>
</x-guest-layout>