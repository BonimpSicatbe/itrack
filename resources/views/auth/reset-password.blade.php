<x-guest-layout>
    <div class="w-full h-full bg-cover bg-center min-h-screen min-w-screen"
        style="background-image: url('{{ asset('images/cvsu-bg-image.jpg') }}');">
        <div class="w-full h-full flex flex-col items-end justify-center min-h-screen p-4 md:p-12">
            <div
                class="flex items-center justify-center w-full sm:w-2/3 md:w-1/2 lg:w-1/3 bg-white shadow rounded-lg h-full max-w-md mx-auto">
                <form method="POST" action="{{ $isAccountSetup ? route('account.update') : route('password.store') }}"
                    class="w-full p-6 h-full flex flex-col gap-4 items-center justify-center">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Different headers based on context -->
                    <div class="w-full text-center mb-2">
                        @if ($isAccountSetup)
                            <h2 class="text-2xl font-bold text-gray-900">Set Up Your Account</h2>
                            <p class="text-sm text-gray-600 mt-2">Create your password to activate your account</p>
                        @else
                            <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>
                            <p class="text-sm text-gray-600 mt-2">Enter your new password below</p>
                        @endif
                    </div>

                    <!-- Email Display - Always readonly and visible -->
                    <div class="w-full">
                        <x-input-label for="email" :value="__('Email Address')" />
                        <div class="mt-1 relative">
                            <input type="email" id="email" name="email"
                                value="{{ old('email', $request->email) }}" readonly required
                                class="block w-full rounded-lg border-gray-300 bg-gray-100 pl-3 pr-10 py-3 text-gray-700 cursor-not-allowed focus:border-green-500 focus:ring-green-500 sm:text-sm border"
                                style="background-color: #f9fafb;">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fa-solid fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password Fields -->
                    <div class="w-full">
                        <x-input-label for="password" :value="__('New Password')" />
                        <div class="mt-1 relative">
                            <x-text-input id="password" class="block w-full pl-3 pr-10 py-3" type="password"
                                name="password" required autocomplete="new-password"
                                placeholder="Enter your new password" />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <button type="button"
                                    class="text-gray-400 hover:text-gray-600 focus:outline-none password-toggle"
                                    data-target="password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="w-full">
                        <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                        <div class="mt-1 relative">
                            <x-text-input id="password_confirmation" class="block w-full pl-3 pr-10 py-3"
                                type="password" name="password_confirmation" required autocomplete="new-password"
                                placeholder="Confirm your new password" />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <button type="button"
                                    class="text-gray-400 hover:text-gray-600 focus:outline-none password-toggle"
                                    data-target="password_confirmation">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end w-full mt-4">
                        <x-primary-button class="w-full justify-center py-3">
                            <i class="fa-solid {{ $isAccountSetup ? 'fa-user-check' : 'fa-key' }} mr-2"></i>
                            {{ $isAccountSetup ? __('Set Up Account') : __('Reset Password') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle functionality
            const toggleButtons = document.querySelectorAll('.password-toggle');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>

    <style>
        /* Additional styling for readonly field */
        input[readonly] {
            background-color: #f9fafb !important;
            border-color: #d1d5db !important;
            color: #374151 !important;
        }

        input[readonly]:focus {
            border-color: #d1d5db !important;
            ring-color: #d1d5db !important;
        }

        /* Password toggle button styling */
        .password-toggle {
            cursor: pointer;
            transition: color 0.2s ease-in-out;
            background: none;
            border: none;
            padding: 0;
        }
    </style>
</x-guest-layout>
