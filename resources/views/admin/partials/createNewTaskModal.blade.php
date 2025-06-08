<input type="checkbox" id="createNewTask" class="modal-toggle" />
<div class="modal modal-bottom sm:modal-middle" role="dialog">
    <div class="modal-box !w-11/12 !max-w-5xl">
        {{-- modal header --}}
        <h3 class="text-lg font-bold">Create New Task</h3>
        {{-- modal body --}}
        <form wire:submit.prevent='create' class="flex flex-col gap-4">
            <div>
                {{-- Task Name --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend w-full">Task Name</legend>
                    <input type="text" wire:model="name" class="input w-full" placeholder="Enter Task Name" />
                    @error('name')
                        <p class="text-red-500 text-sm w-full truncate overflow-auto">{{ $message }}</p>
                    @enderror
                </fieldset>

                {{-- Task Description --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend w-full">Task Description</legend>
                    <textarea wire:model="description" class="textarea w-full" placeholder="Enter Task Description"></textarea>
                    @error('description')
                        <p class="text-red-500 text-sm w-full truncate overflow-auto">{{ $message }}</p>
                    @enderror
                </fieldset>

                {{-- Task Target --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend w-full">Task Target</legend>
                    <select wire:model="taskTarget" class="select w-full">
                        <option value="" disabled selected>Select Target</option>
                        <option value="college">College</option>
                        <option value="department">Department</option>
                    </select>
                    @error('taskTarget')
                        <span class="text-red-500 text-sm w-full truncate overflow-auto">{{ $message }}</span>
                    @enderror
                </fieldset>

                {{-- College Select --}}
                <fieldset class="fieldset" @if($taskTarget !== 'college') style="display:none;" @endif>
                    <legend class="fieldset-legend w-full">Target College</legend>
                    <select wire:model="targetCollege" class="select w-full">
                        <option value="" disabled selected>Select College</option>
                        @foreach (App\Models\College::all() as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </select>
                    @error('targetCollege')
                        <span class="text-red-500 text-sm w-full truncate overflow-auto">{{ $message }}</span>
                    @enderror
                </fieldset>

                {{-- Department Select --}}
                <fieldset class="fieldset" @if($taskTarget !== 'department') style="display:none;" @endif>
                    <legend class="fieldset-legend w-full">Target Department</legend>
                    <select wire:model="targetDepartment" class="select w-full">
                        <option value="" disabled selected>Select Department</option>
                        @foreach (App\Models\Department::all() as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('targetDepartment')
                        <span class="text-red-500 text-sm w-full truncate overflow-auto">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend w-full">Upload File</legend>
                    <input wire:model='uploadedFile' type="file" class="file-input file-input-bordered w-full" />
                    @error('uploadedFile')
                        <label class="label text-red-500 text-sm w-full truncate overflow-auto">{{ $message }}</label>
                    @enderror
                </fieldset>
            </div>

            {{-- Submit --}}
            <div class="flex flex-row gap-4 justify-end w-full items-center text-end">
                {{-- <button type="button" class="btn btn-sm btn-default uppercase"
                    onclick="createNewTask.close()">Close</button> --}}
                <label for="createNewTask" class="btn btn-sm btn-default uppercase">Close</label>
                <button type="submit" class="btn btn-sm btn-success uppercase">Create</button>
            </div>
        </form>
    </div>
</div>
