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
                <label for="uploadFile" class="btn btn-sm btn-default"><i class="fa-solid fa-file-import"></i></label>
                <label for="createFolder" class="btn btn-sm btn-default"><i class="fa-solid fa-folder-plus"></i></label>
            </div>
            {{-- content | body --}}
            <div
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4 overflow-x-auto w-full">
                @for ($i = 0; $i < 15; $i++)
                    <a href=""
                        class="text-center border rounded-lg p-4 w-full max-w-[300px] hover:bg-gray-200 transition-all">
                        <i class="fa-solid fa-file-lines text-3xl"></i>
                        <div class="w-full text-sm font-bold truncate">Lorem ipsum dolor sit.</div>
                        {{-- <div class="w-full text-sm text-gray-500 truncate">description</div>
                        <div class="w-full text-xs text-gray-500 truncate">created_at</div> --}}
                    </a>
                @endfor
            </div>
        </div>
    </div>

    {{-- Upload File --}}
    <input type="checkbox" id="uploadFile" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Upload File</h3>
            <form action="" method="POST">
                @csrf
                <div class="form-control w-full">
                    <label class="label"><span class="label-text">Folder Name</span></label>
                    <input type="text" name="folder_name" class="input input-bordered" required>
                </div>
                <input type="hidden" name="parent_id" value="{{ request('folder') ?? null }}" />
                <div class="modal-action">
                    <label for="createFolder" class="btn btn-sm">Cancel</label>
                    <button type="submit" class="btn btn-sm btn-primary">Create</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="uploadFile"></label>
    </div>

    {{-- Create Folder --}}
    <input type="checkbox" id="createFolder" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Create Folder</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-control w-full">
                    <label class="label"><span class="label-text">Select File</span></label>
                    <input type="file" name="file" class="file-input file-input-bordered w-full" required>
                </div>
                <input type="hidden" name="parent_id" value="{{ request('folder') ?? null }}" />
                <div class="modal-action">
                    <label for="uploadFile" class="btn btn-sm">Cancel</label>
                    <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="createFolder"></label>
    </div>
</x-user.app-layout>
