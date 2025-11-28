<form wire:submit.prevent="store" class="w-full p-6 h-full grid grid-cols-2 gap-2 items-center justify-center">
    @csrf

    <!-- First Name -->
    <fieldset class="fieldset">
        <label>First name</label>
        <input class="input input-md w-full" type="text" wire:model.live="firstname" placeholder="First name">
        @error('firstname')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Middle Name -->
    <fieldset class="fieldset">
        <label>Middle name</label>
        <input class="input input-md w-full" type="text" wire:model.live="middlename" placeholder="Middle name">
        @error('middlename')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Last Name -->
    <fieldset class="fieldset">
        <label>Last name</label>
        <input class="input input-md w-full" type="text" wire:model.live="lastname" placeholder="Last name">
        @error('lastname')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Extension Name -->
    <fieldset class="fieldset">
        <label>Suffix (Sr., Jr. III., etc)</label>
        <input class="input input-md w-full" type="text" wire:model.live="extensionname" placeholder="Suffix (Sr., Jr. III., etc)">
        @error('extensionname')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Email -->
    <fieldset class="fieldset">
        <label>Email</label>
        <input class="input input-md w-full" type="email" wire:model.live="email" placeholder="Email">
        @error('email')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- College -->
    <fieldset class="fieldset">
        <label>Select College</label>
        <select class="select w-full text-gray-500" wire:model.live="college_id" placeholder="Select College">
            <option value="">Select College</option>
            @forelse($colleges ?? [] as $college)
                <option value="{{ $college->id }}">{{ $college->name }}</option>
            @empty
                <option disabled>No colleges available</option>
            @endforelse
        </select>
        @error('college_id')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Position -->
    <fieldset class="fieldset">
        <label>Select Position</label>
        <select class="select w-full text-gray-500" wire:model.live="position" placeholder="Select Position">
            <option value="">Select Position</option>
            <option value="Associate Professor I">Associate Professor I</option>
            <option value="Associate Professor II">Associate Professor II</option>
            <option value="IT Officer">IT Officer</option>
        </select>
        @error('position')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Teaching Started At -->
    <fieldset class="fieldset">
        <label>Teaching Started At</label>
        <input class="input input-md w-full" max="{{ now()->toDateString() }}" type="date" wire:model.live="teaching_started_at" placeholder="Teaching Started At">
        @error('teaching_started_at')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Password -->
    <fieldset class="fieldset">
        <label>Password</label>
        <input class="input input-md w-full" type="password" wire:model.live="password" placeholder="Password">
        @error('password')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <!-- Password Confirmation -->
    <fieldset class="fieldset">
        <label>Confirm Password</label>
        <input class="input input-md w-full" type="password" wire:model.live="password_confirmation" placeholder="Confirm Password">
        @error('password_confirmation')
            <span class="error">{{ $message }}</span>
        @enderror
    </fieldset>

    <div class="col-span-2 text-center w-full">
    <button class="btn btn-md btn-success" type="submit">Register Account</button>
    </div>
</form>
