@php
    $layout = 'user.app-layout';
    if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin')) {
        $layout = 'admin.app-layout';
    }
@endphp

<x-dynamic-component :component="$layout">
    <div class="">
        <div class="p-4 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile Information Form -->
                <div class="bg-white sm:rounded-lg p-6">
                    <header class="p-6">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    {{ __('Profile Information') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __("Update your account's profile information and email address.") }}
                                </p>
                            </div>
                        </div>
                    </header>
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Update Password Form -->
                <div class="bg-white sm:rounded-lg p-6">
                    <header class="pb-6">
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
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>