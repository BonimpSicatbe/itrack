<form wire:submit.prevent="store" class="w-full p-6 h-full grid grid-cols-2 gap-4 items-center justify-center">
    <!-- Logo at the very top -->
    <div class="col-span-2 flex justify-center mb-6">
        <img src="{{ asset('images/logo-title.png') }}" alt="CvSU iTrack Logo" class="h-20 w-auto">
    </div>

    <!-- First Name -->
    <fieldset class="fieldset">
        <label class="fieldset-label">First name <span class="text-red-500">*</span></label>
        <input class="input input-md w-full @error('firstname') input-error @enderror" type="text" wire:model.blur="firstname" placeholder="First name">
        @error('firstname')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Middle Name -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Middle name</label>
        <input class="input input-md w-full @error('middlename') input-error @enderror" type="text" wire:model.blur="middlename" placeholder="Middle name">
        @error('middlename')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Last Name -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Last name <span class="text-red-500">*</span></label>
        <input class="input input-md w-full @error('lastname') input-error @enderror" type="text" wire:model.blur="lastname" placeholder="Last name">
        @error('lastname')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Extension Name -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Suffix (Sr., Jr. III., etc)</label>
        <input class="input input-md w-full @error('extensionname') input-error @enderror" type="text" wire:model.blur="extensionname" placeholder="Suffix (Sr., Jr. III., etc)">
        @error('extensionname')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Email -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Email <span class="text-red-500">*</span></label>
        <input class="input input-md w-full @error('email') input-error @enderror" type="email" wire:model.blur="email" placeholder="user@cvsu.edu.ph">
        @error('email')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- College -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Select College <span class="text-red-500">*</span></label>
        <select class="select select-md w-full @error('college_id') select-error @enderror" wire:model.blur="college_id">
            <option value="">Select College</option>
            @forelse($colleges ?? [] as $college)
                <option value="{{ $college->id }}">{{ $college->name }}</option>
            @empty
                <option disabled>No colleges available</option>
            @endforelse
        </select>
        @error('college_id')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Position -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Select Position <span class="text-red-500">*</span></label>
        <select class="select select-md w-full @error('position') select-error @enderror" wire:model.blur="position">
            <option value="">Select Position</option>
            <option value="Associate Professor I">Associate Professor I</option>
            <option value="Associate Professor II">Associate Professor II</option>
            <option value="IT Officer">IT Officer</option>
            <option value="Instructor">Instructor</option>
            <option value="Assistant Professor">Assistant Professor</option>
            <option value="Associate Professor">Associate Professor</option>
            <option value="Professor">Professor</option>
        </select>
        @error('position')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Teaching Started At -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Teaching Started At <span class="text-red-500">*</span></label>
        <input class="input input-md w-full @error('teaching_started_at') input-error @enderror" max="{{ now()->toDateString() }}" type="date" wire:model.blur="teaching_started_at">
        @error('teaching_started_at')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Password -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Password <span class="text-red-500">*</span></label>
        <input class="input input-md w-full @error('password') input-error @enderror" type="password" wire:model.blur="password" placeholder="Password">
        @error('password')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Password Confirmation -->
    <fieldset class="fieldset">
        <label class="fieldset-label">Confirm Password <span class="text-red-500">*</span></label>
        <input class="input input-md w-full @error('password_confirmation') input-error @enderror" type="password" wire:model.blur="password_confirmation" placeholder="Confirm Password">
        <!-- Real-time password match indicator -->
        <div class="mt-2 text-xs">
            @if($password && $password_confirmation)
                @if($password === $password_confirmation)
                    <span class="text-green-600 font-medium">✓ Passwords match</span>
                @else
                    <span class="text-red-600 font-medium">✗ Passwords do not match</span>
                @endif
            @endif
        </div>
    </fieldset>

    <!-- Submit Button -->
    <div class="col-span-2 text-center w-full mt-4">
        <button class="btn btn-md btn-success w-full md:w-1/2" type="submit" wire:loading.attr="disabled" wire:target="store">
            <span wire:loading.remove wire:target="store">Register Account</span>
            <span wire:loading wire:target="store">
                <span class="loading loading-spinner loading-sm"></span>
                Processing...
            </span>
        </button>
        
        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="link link-primary text-sm">Already have an account? Login here</a>
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