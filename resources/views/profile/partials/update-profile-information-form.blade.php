<section class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <!-- Scrollable container for form fields -->
        <div class="max-h-[60vh] overflow-y-auto p-4 -mr-4 space-y-6">
            <div class="grid gap-6">
                <!-- First Name Field -->
                <div>
                    <label for="firstname" class="block text-xs font-medium tracking-wide uppercase text-gray-700 mb-2">
                        {{ __('First Name') }}
                    </label>
                    <div class="relative">
                        <input 
                            id="firstname" 
                            name="firstname" 
                            type="text" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                            value="{{ old('firstname', $user->firstname) }}" 
                            required 
                            autofocus 
                            autocomplete="given-name"
                            placeholder="Enter your first name"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('firstname')" />
                </div>

                <!-- Middle Name Field -->
                <div>
                    <label for="middlename" class="block text-xs font-medium tracking-wide uppercase text-gray-700 mb-2">
                        {{ __('Middle Name') }}
                    </label>
                    <div class="relative">
                        <input 
                            id="middlename" 
                            name="middlename" 
                            type="text" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                            value="{{ old('middlename', $user->middlename) }}" 
                            autocomplete="additional-name"
                            placeholder="Enter your middle name (optional)"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('middlename')" />
                </div>

                <!-- Last Name Field -->
                <div>
                    <label for="lastname" class="block text-xs font-medium tracking-wide uppercase text-gray-700 mb-2">
                        {{ __('Last Name') }}
                    </label>
                    <div class="relative">
                        <input 
                            id="lastname" 
                            name="lastname" 
                            type="text" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                            value="{{ old('lastname', $user->lastname) }}" 
                            required 
                            autocomplete="family-name"
                            placeholder="Enter your last name"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('lastname')" />
                </div>

                <!-- Suffix Field -->
                <div>
                    <label for="extensionname" class="block text-xs font-medium tracking-wide uppercase text-gray-700 mb-2">
                        {{ __('Suffix') }} <span class="text-gray-500 font-normal">(Sr., Jr., III, etc.)</span>
                    </label>
                    <div class="relative">
                        <input 
                            id="extensionname" 
                            name="extensionname" 
                            type="text" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                            value="{{ old('extensionname', $user->extensionname) }}" 
                            autocomplete="honorific-suffix"
                            placeholder="e.g., Jr., Sr., III (optional)"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('extensionname')" />
                </div>

                <!-- Position Field -->
                <div>
                    <label for="position" class="block text-xs font-medium tracking-wide uppercase text-gray-700 mb-2">
                        {{ __('Position') }}
                    </label>
                    <div class="relative">
                        <input 
                            id="position" 
                            name="position" 
                            type="text" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                            value="{{ old('position', $user->position) }}" 
                            autocomplete="organization-title"
                            placeholder="Enter your position (optional)"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('position')" />
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-xs font-medium tracking-wide uppercase text-gray-700 mb-2">
                        {{ __('Email') }}
                    </label>
                    <div class="relative">
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                            value="{{ old('email', $user->email) }}" 
                            required 
                            autocomplete="username"
                            placeholder="Enter your email address"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        {{ __('Email Verification Required') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>{{ __('Your email address is unverified.') }}</p>
                                    </div>
                                    <div class="mt-3">
                                        <button 
                                            form="send-verification" 
                                            class="text-sm font-medium text-yellow-800 underline hover:text-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 rounded"
                                        >
                                            {{ __('Click here to re-send the verification email.') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @if (session('status') === 'verification-link-sent')
                                <div class="mt-3 text-sm font-medium text-green-600 bg-green-50 px-3 py-2 rounded-xl">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Teaching Started At Field -->
                <div>
                    <label for="teaching_started_at" class="block text-xs font-medium tracking-wide uppercase text-gray-700 mb-2">
                        {{ __('Teaching Started At') }}
                    </label>
                    <div class="relative">
                        <input 
                            id="teaching_started_at" 
                            name="teaching_started_at" 
                            type="date" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                            value="{{ old('teaching_started_at', $user->teaching_started_at ? $user->teaching_started_at->format('Y-m-d') : '') }}" 
                            autocomplete="off"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('teaching_started_at')" />
                </div>
            </div>
        </div>

        <!-- Fixed footer with save button -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="flex items-center">
                @if (session('status') === 'profile-updated')
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform translate-y-2"
                        x-transition:enter-end="opacity-100 transform-y-0"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform translate-y-2"
                        x-init="setTimeout(() => show = false, 3000)"
                        class="flex items-center space-x-2 text-sm text-green-600 bg-green-50 px-3 py-2 rounded-xl"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ __('Profile updated successfully!') }}</span>
                    </div>
                @endif
            </div>
            
            <button 
                type="submit"
                class="inline-flex items-center px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Save Changes') }}
            </button>
        </div>
    </form>
</section>