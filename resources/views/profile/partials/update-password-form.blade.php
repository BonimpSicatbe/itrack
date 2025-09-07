<section class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <header class="border-b border-gray-100 pb-6 mb-8">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('Update Password') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Ensure your account is using a long, random password to stay secure.') }}
                </p>
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div class="grid gap-6">
            <div>
                <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Current Password') }}
                </label>
                <div class="relative">
                    <input 
                        id="update_password_current_password" 
                        name="current_password" 
                        type="password" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 pr-10"
                        autocomplete="current-password"
                        placeholder="Enter current password"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('New Password') }}
                </label>
                <div class="relative">
                    <input 
                        id="update_password_password" 
                        name="password" 
                        type="password" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 pr-10"
                        autocomplete="new-password"
                        placeholder="Enter new password"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Confirm Password') }}
                </label>
                <div class="relative">
                    <input 
                        id="update_password_password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 pr-10"
                        autocomplete="new-password"
                        placeholder="Confirm new password"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between pt-6">
            <div class="flex items-center">
                @if (session('status') === 'password-updated')
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform translate-y-2"
                        x-init="setTimeout(() => show = false, 3000)"
                        class="flex items-center space-x-2 text-sm text-green-600 bg-green-50 px-3 py-2 rounded-xl"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ __('Password updated successfully!') }}</span>
                    </div>
                @endif
            </div>
            
            <button 
                type="submit"
                class="inline-flex items-center px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Update Password') }}
            </button>
        </div>
    </form>
</section>