<x-guest-layout>
    <div class="w-full h-full bg-cover bg-center min-h-screen min-w-screen"
        style="background-image: url('{{ asset('images/cvsu-bg-image.jpg') }}');">
        {{-- login form --}}
        <div class="w-full h-full flex flex-col items-end justify-center min-h-screen p-4 md:p-12">
            <div class="flex items-center justify-center w-full bg-white shadow rounded-lg max-w-4xl mx-auto">
                <form method="POST" action="{{ route('register') }}"
                    class="w-full p-6 h-full grid grid-cols-2 gap-2 items-center justify-center">
                    <div class="grid grid-cols-3 gap-x-4 w-full text-center items-center col-span-2">
                        <x-application-logo class="w-20 h-20 text-gray-500" />
                        <div class="text-2xl uppercase grow font-black">Register Account</div>
                        <div class="grow"></div>
                    </div>
                    @csrf

                    <x-text-fieldset type="text" name="firstname" label="First name" id="firstname" />
                    <x-text-fieldset type="text" name="middlename" label="Middle name" id="middlename" />
                    <x-text-fieldset type="text" name="lastname" label="Last name" id="lastname" />
                    <x-text-fieldset type="text" name="extensionname" label="Extension name" id="extensionname" />
                    <x-text-fieldset type="email" name="email" label="Email" id="email" />
                    <x-select-fieldset name="college_id" id="college_id" label="College ID">
                        <option value="" disabled {{ old('college_id') ? '' : 'selected' }}>Select College ID
                        </option>
                        @forelse($colleges ?? [] as $college)
                            <option value="{{ $college->id }}"
                                {{ old('college_id') == $college->id ? 'selected' : '' }}>
                                {{ $college->name }}
                            </option>
                        @empty
                            <option value="" disabled selected>No colleges available</option>
                        @endforelse
                    </x-select-fieldset>
                    <x-text-fieldset type="text" name="position" label="Position" id="position" />
                    <x-text-fieldset type="date" name="teaching_started_at" label="Teaching Started At"
                        id="teaching_started_at" />
                    <x-text-fieldset type="password" name="password" label="Password" id="password" />
                    <x-text-fieldset type="password" name="password_confirmation" label="Confirm Password"
                        id="password_confirmation" />

                    <div class="text-center col-span-2">
                        <span>Already have an account? </span><a href="{{ route('login') }}"
                            class="link hover:text-blue-500">Login</a>
                    </div>
                    <div class="text-center col-span-2">
                        <x-primary-button>{{ __('Register Account') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
