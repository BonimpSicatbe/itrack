<x-user.app-layout>
    <div class="flex flex-col gap-4 h-full w-full">
        <div class="flex flex-col gap-4 p-4 bg-white rounded-lg h-full">
            {{-- header --}}
            <div class="text-lg uppercase font-bold">Notifications</div>

            {{-- @dd(App\Models\Requirement::where('assigned_to', auth()->user()->college->name)->get(), Auth::user()->notifications) --}}

            @livewire('user.notification.notification')
        </div>
    </div>
</x-user.app-layout>
