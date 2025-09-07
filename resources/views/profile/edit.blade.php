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
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h2>
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Update Password Form -->
                <div class="bg-white sm:rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Update Password</h2>
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>