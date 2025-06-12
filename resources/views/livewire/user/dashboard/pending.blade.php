<div class="flex flex-col gap-2 p-4 overflow-hidden bg-white rounded-lg">
    {{-- header --}}
    <div class="flex flex-row items-center justify-between w-full">
        <div class="text-lg uppercase font-bold">Pendings</div>
        <a href="" class="text-green-500 hover:text-green-700 text-xs hover:link transition-all">see more <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    {{-- list --}}
    <div class="flex flex-row gap-4 overflow-x-auto w-full">
        @for ($i = 0; $i < 10; $i++)
            <a href="" class="border rounded-lg p-2 flex flex-col min-w-[300px] hover:bg-gray-200 transition-all">
                <div class="flex flex-row items-center gap-4 justify-between w-full">
                    <div class="text-lg font-bold">Lorem, ipsum dolor.</div>
                    <div class="text-xs text-gray-500">00m</div>
                </div>
                <div class="text-sm">Lorem, ipsum dolor.</div>
            </a>
        @endfor
    </div>
</div>
