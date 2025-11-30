<form wire:submit.prevent="store" class="w-full p-6 h-full grid grid-cols-2 gap-4 items-center justify-center">
    <!-- Logo at the very top -->
    <div class="col-span-2 flex justify-center mb-6">
        <img src="{{ asset('images/logo-title.png') }}" alt="CvSU iTrack Logo" class="h-20 w-auto">
    </div>

    <!-- First Name -->
    <div class="w-full">
        <x-input-label for="firstname" :value="__('First name')" />
        <x-text-input id="firstname" class="block mt-1 w-full" type="text" wire:model.blur="firstname" placeholder="First name" required autofocus />
        <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
    </div>

    <!-- Middle Name -->
    <div class="w-full">
        <x-input-label for="middlename" :value="__('Middle name')" />
        <x-text-input id="middlename" class="block mt-1 w-full" type="text" wire:model.blur="middlename" placeholder="Middle name" />
        <x-input-error :messages="$errors->get('middlename')" class="mt-2" />
    </div>

    <!-- Last Name -->
    <div class="w-full">
        <x-input-label for="lastname" :value="__('Last name')" />
        <x-text-input id="lastname" class="block mt-1 w-full" type="text" wire:model.blur="lastname" placeholder="Last name" required />
        <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
    </div>

    <!-- Extension Name -->
    <div class="w-full">
        <x-input-label for="extensionname" :value="__('Suffix (Sr., Jr. III., etc)')" />
        <x-text-input id="extensionname" class="block mt-1 w-full" type="text" wire:model.blur="extensionname" placeholder="Suffix (Sr., Jr. III., etc)" />
        <x-input-error :messages="$errors->get('extensionname')" class="mt-2" />
    </div>

    <!-- Email -->
    <div class="w-full">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" wire:model.blur="email" placeholder="user@cvsu.edu.ph" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- College -->
    <div class="w-full">
        <x-input-label for="college_id" :value="__('Select College')" />
        <select id="college_id" class="block mt-1 w-full rounded-lg border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 @error('college_id') border-red-500 @enderror" wire:model.blur="college_id">
            <option value="">Select College</option>
            @forelse($colleges ?? [] as $college)
                <option value="{{ $college->id }}">{{ $college->name }}</option>
            @empty
                <option disabled>No colleges available</option>
            @endforelse
        </select>
        <x-input-error :messages="$errors->get('college_id')" class="mt-2" />
    </div>

    <!-- Position -->
    <div class="w-full">
        <x-input-label for="position" :value="__('Select Position')" />
        <select id="position" class="block mt-1 w-full rounded-lg border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 @error('position') border-red-500 @enderror" wire:model.blur="position">
            <option value="">Select Position</option>
            <option value="Associate Professor I">Associate Professor I</option>
            <option value="Associate Professor II">Associate Professor II</option>
            <option value="IT Officer">IT Officer</option>
            <option value="Instructor">Instructor</option>
            <option value="Assistant Professor">Assistant Professor</option>
            <option value="Associate Professor">Associate Professor</option>
            <option value="Professor">Professor</option>
        </select>
        <x-input-error :messages="$errors->get('position')" class="mt-2" />
    </div>

    <!-- Teaching Started At -->
    <div class="w-full">
        <x-input-label for="teaching_started_at" :value="__('Teaching Started At')" />
        <x-text-input id="teaching_started_at" class="block mt-1 w-full" type="date" wire:model.blur="teaching_started_at" max="{{ now()->toDateString() }}" required />
        <x-input-error :messages="$errors->get('teaching_started_at')" class="mt-2" />
    </div>

    <!-- Password -->
    <div class="w-full relative">
        <x-input-label for="password" :value="__('Password')" />
        <div class="relative">
            <x-text-input id="password" class="block mt-1 w-full pr-10" type="password" wire:model.blur="password" required autocomplete="new-password" />
            <button type="button" 
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-900 mt-1"
                onclick="togglePassword('password')">
                <svg id="eye-icon-password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Password Confirmation -->
    <div class="w-full relative">
        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
        <div class="relative">
            <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10" type="password" wire:model.blur="password_confirmation" required autocomplete="new-password" />
            <button type="button" 
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-900 mt-1"
                onclick="togglePassword('password_confirmation')">
                <svg id="eye-icon-password_confirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
        <!-- Real-time password match indicator -->
        <div class="mt-2 text-xs">
            @if($password && $password_confirmation)
                @if($password === $password_confirmation)
                    <span class="text-green-600 font-medium">Passwords match</span>
                @else
                    <span class="text-red-600 font-medium">Passwords do not match</span>
                @endif
            @endif
        </div>
    </div>

    <!-- Submit Button -->
    <div class="col-span-2 text-center w-full mt-4">
        <button class="w-full md:w-1/2 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 transition duration-150 ease-in-out" 
                type="submit" 
                wire:loading.attr="disabled" 
                wire:target="store">
            <span wire:loading.remove wire:target="store">REGISTER ACCOUNT</span>
            <span wire:loading wire:target="store">
                <span class="loading loading-spinner loading-sm"></span>
                Processing...
            </span>
        </button>
        
        <div class="mt-4 text-center text-sm">
            Already have an account? 
            <a href="{{ route('login') }}" class="underline text-green-600 hover:text-green-700 font-medium">Login here</a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="col-span-2 alert alert-success mt-4">
            <div class="flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 mx-2 stroke-current"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path> 
                </svg>
                <label>{{ session('success') }}</label>
            </div>
        </div>
    @endif

    @if ($errors->any() && !$errors->has('firstname') && !$errors->has('email') && !$errors->has('password') && !$errors->has('college_id') && !$errors->has('position') && !$errors->has('teaching_started_at'))
        <div class="col-span-2 alert alert-error mt-4">
            <div class="flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 mx-2 stroke-current"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path> 
                </svg>
                <label>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </label>
            </div>
        </div>
    @endif
</form>

<script>
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const eyeIcon = document.getElementById(`eye-icon-${fieldId}`);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    }
</script>