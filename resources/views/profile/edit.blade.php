@php
    $layout = 'user.app-layout';
    if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin')) {
        $layout = 'admin.app-layout';
    }
@endphp

<x-dynamic-component :component="$layout">
    <div class="min-h-screen w-full bg-white">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
                <p class="text-lg text-gray-600 mt-2">Manage your account information and security settings</p>
            </div>
            
            <!-- Success Messages -->
            @if(session('status') == 'profile-updated')
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">Profile information has been updated successfully.</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(session('status') == 'password-updated')
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">Password has been updated successfully.</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column - Navigation & Info -->
                <div class="space-y-6">
                    <!-- Navigation -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Settings</h2>
                        <div class="space-y-2">
                            <div class="flex items-center p-3 rounded-md bg-white shadow-sm border-l-4" style="border-left-color: #107054;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" style="color: #107054;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium" style="color: #107054;">Profile Information</span>
                            </div>
                            <div class="flex items-center p-3 rounded-md hover:bg-gray-100 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="font-medium text-gray-700">Update Password</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Box -->
                    <div class="bg-blue-50 rounded-lg p-5 border-l-4" style="border-left-color: #73E2A7;">
                        <div class="flex">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" style="color: #107054;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h3 class="font-medium" style="color: #107054;">Security Tip</h3>
                                <p class="text-sm text-gray-600 mt-1">Ensure your account is using a long, random password to stay secure.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Forms -->
                <div class="space-y-8">
                    <!-- Profile Information Form -->
                    <form method="POST" action="{{ route('profile.update') }}" class="bg-white rounded-lg border border-gray-200 p-6">
                        @csrf
                        @method('PATCH')
                        
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Profile Information</h2>
                        
                        <div class="space-y-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                @php
                                    // Reconstruct the full name from the user's name parts
                                    $fullName = trim(implode(' ', [
                                        $user->firstname,
                                        $user->middlename,
                                        $user->lastname,
                                        $user->extensionname
                                    ]));
                                @endphp
                                <input type="text" id="name" name="name" value="{{ old('name', $fullName) }}" 
                                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <button type="submit" class="w-full py-3 px-4 rounded-md text-white font-medium transition-colors" style="background-color: #107054; hover:background-color: #0d5e43;">
                                Save Changes
                            </button>
                        </div>
                    </form>
                    
                    <!-- Update Password Form -->
                    <form method="POST" action="{{ route('password.update') }}" class="bg-white rounded-lg border border-gray-200 p-6">
                        @csrf
                        @method('PUT')
                        
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Update Password</h2>
                        
                        <div class="space-y-5">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" id="current_password" name="current_password" placeholder="Enter current password" 
                                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('current_password', 'updatePassword')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter new password" 
                                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('password', 'updatePassword')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" 
                                    class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <button type="submit" class="w-full py-3 px-4 rounded-md text-white font-medium transition-colors" style="background-color: #107054; hover:background-color: #0d5e43;">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Last Updated Info -->
            <div class="mt-12 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
                Last updated: {{ now()->format('M d, Y h:i A') }}
            </div>
        </div>
    </div>
</x-dynamic-component>