<x-user.app-layout>
    <div class="flex flex-col gap-4 h-full w-full">
        {{-- file manager --}}
        <div class="flex flex-col gap-4 p-4 bg-white rounded-lg h-full">
            {{-- header --}}
            <div class="text-lg uppercase font-bold">File Manager</div>
            {{-- action fields --}}
            <div class="flex flex-row gap-4 items-center w-full">
                <div class="breadcrumbs text-sm grow truncate overflow-auto"
                    style="scrollbar-width: none; -ms-overflow-style: none;">
                    <ul>
                        <li>
                            <a>
                                <i class="fa-regular fa-folder"></i>
                                File Manager
                            </a>
                        </li>
                    </ul>
                </div>
                <style>
                    .breadcrumbs::-webkit-scrollbar {
                        display: none;
                    }
                </style>

                {{-- buttons --}}
                <input type="text" name="search" id="search" class="input input-bordered input-sm w-128"
                    placeholder="Search files..." />
            </div>

            {{-- content | body --}}
            @isset($files)
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4 overflow-x-auto w-full">
                    @foreach ($files as $file)
                        <div class="flex items-center justify-center w-full h-full">
                            <i class="fa-solid fa-files text-3xl"></i>
                            <div class="text-md font-bold">Lorem, ipsum dolor.</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col gap-4 text-gray-500 items-center justify-center w-full h-full">
                    <div class="flex flex-row gap-4">
                        <i class="fa-solid fa-folder-open text-3xl"></i>
                        <div class="divider divider-horizontal"></div>
                        <i class="fa-solid fa-file text-3xl"></i>
                    </div>
                    <div class="text-xl font-bold">No files found.</div>
                </div>
            @endisset
        </div>
    </div>
</x-user.app-layout>
