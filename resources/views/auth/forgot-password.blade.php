<x-guest-layout>
    <div class="w-full h-full bg-cover bg-center min-h-screen min-w-screen"
        style="background-image: url('{{ asset('images/bg-1.png') }}');">
        {{-- reset password form --}}
        <div class="w-full h-full flex flex-col items-end justify-center border border-white min-h-screen p-4 md:p-12">
            <div
                class="flex items-center justify-center w-full sm:w-2/3 md:w-1/2 lg:w-1/3 bg-white shadow rounded-lg h-full max-w-md mx-auto">
                <form method="POST" action="{{ route('password.email') }}"
                    class="w-full p-4 h-full flex flex-col gap-2 items-center justify-center">
                    <div class="inline-flex items-center gap-4 w-full justify-center">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </div>
                    @csrf

                    <!-- Email Address -->
                    <div class="w-full">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                            :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <div class="flex items-center justify-end w-full">
                        <x-primary-button class="w-full justify-center">
                            {{ __('Email Password Reset Link') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
