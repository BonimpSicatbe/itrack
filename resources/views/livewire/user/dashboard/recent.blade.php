<div class="flex flex-col p-4 overflow-hidden bg-white rounded-lg">
    {{-- heading --}}
    <div class="flex flex-row items-center justify-between w-full">
        <div class="text-lg uppercase font-bold">Recents</div>
        <a href="" class="text-green-500 hover:text-green-700 text-xs hover:link transition-all">see more <i
                class="fa-solid fa-chevron-right"></i></a>
    </div>
    {{-- list --}}
    <div class="flex flex-row gap-4 overflow-x-auto w-full">
        @for ($i = 0; $i < 10; $i++)
            <a href="" class="border rounded-lg p-2 min-w-[300px] hover:bg-gray-200 transition-all">
                <div class="w-full text-sm font-bold">Lorem ipsum dolor sit.</div>
                <div class="w-full text-sm text-gray-500">Lorem, ipsum dolor.</div>
                <div class="w-full text-xs text-gray-500">month 00, 0000</div>
            </a>
        @endfor
    </div>
</div>
