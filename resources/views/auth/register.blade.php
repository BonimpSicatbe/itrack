<x-guest-layout>
    <div class="w-full h-full bg-cover bg-center min-h-screen min-w-screen"
        style="background-image: url('{{ asset('images/bg.png') }}');">
        {{-- login form --}}
        <div class="w-full h-full flex flex-col items-end justify-center min-h-screen p-4 md:p-12">
            <div class="flex items-center justify-center w-full bg-white shadow rounded-lg max-w-4xl mx-auto">
                @livewire('register-user-controller')
            </div>
        </div>
    </div>
</x-guest-layout>
