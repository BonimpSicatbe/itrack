<div class="w-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4 px-6 pt-6">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-xl font-semibold text-green-700">E-Signature Management</h3>
                <p class="text-sm text-gray-600">| Manage system e-signatures and their permissions.</p>
            </div>
        </div>
        <!-- The button to open modal -->
        <label for="add_e_signature_modal"
            class="px-5 py-2 bg-green-600 text-white font-semibold rounded-xl text-sm cursor-pointer">Add
            E-Signature</label>
        {{-- <button wire:click="openAddUserModal"
            class="px-5 py-2 bg-green-600 text-white font-semibold rounded-xl text-sm cursor-pointer">
            <i class="fa-solid fa-plus mr-2"></i>Add E-Signature
        </button> --}}
    </div>

    <!-- Divider -->
    <div class="border-b border-gray-200 mb-4"></div>

    <!-- Put this part before </body> tag -->
    <input type="checkbox" id="add_e_signature_modal" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="p-0 modal-box w-11/12 max-w-2xl">
            <h3 class="text-lg font-bold bg-green-800 p-4 text-white"><i class="fa-solid fa-pen-nib"></i> Add
                E-Signature</h3>
            <form wire:submit.live='createNewESignature' class="space-y-4 p-6">
                {{-- select user --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Signature Owner</legend>
                    <select wire:model.live="signature_owner" id="signature_owner" class="select select-md w-full">
                        <option value="" selected disabled>Select User</option>
                        @forelse ($users as $user)
                            <option value="{{ $user->id }}"><span
                                    class="block">{{ $user->firstname . ' ' . $user->middlename . ' ' . $user->lastname }}</span>
                                - <span class="text-xs text-gray-500">{{ $user->position }}</span></option>
                        @empty
                            <option value="" disabled>No users available</option>
                        @endforelse
                    </select>
                    @error('signature_owner')
                        <span class="label">{{ $message }}</span>
                    @enderror
                </fieldset>

                {{-- file upload --}}
                <fieldset class="fieldset">
                    <legend for="e_signature">E-Signature</legend>
                    <input wire:model.live='e_signature' type="file" class="file-input w-full" />
                    @error('e_signature')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                    @if (!$errors->has('e_signature'))
                        <label class="label">Upload png file. Max size 2MB</label>
                    @endif
                </fieldset>

                {{-- submit button --}}
                <div class="text-end">
                    <label for="add_e_signature_modal" class="btn btn-sm btn-default">Cancel</label>
                    <button type="submit" class="btn btn-sm btn-success">Create E-Signature</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="add_e_signature_modal">Close</label>
    </div>

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto bg-white">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <!-- Table Head -->
                <thead>
                    <tr>
                        <th class="bg-green-800 text-white">ID</th>
                        <th class="bg-green-800 text-white">Owner</th>
                        <th class="bg-green-800 text-white">E-Signature Preview</th>
                        <th class="bg-green-800 text-white">Uploaded At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($signatures as $signature)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $signature->user->firstname . ' ' . $signature->user->lastname }}</td>
                            <td>{{ $signature->created_at->format('F d, Y - h:i A') }}</td>
                            <td>
                                @if ($signature->file_path)
                                    <img src="{{ asset('storage/' . $signature->file_path) }}" alt="E-Signature" class="w-20 h-auto" />
                                @else
                                    No Signature
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td>No Signatures Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
