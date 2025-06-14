<x-admin.app-layout>
    @livewire('admin.dashboard.overview')

    <div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
        <div class="flex flex-row gap-4 justify-between items-center w-full">
            <div class="text-lg font-bold uppercase">Requirement</div>
            <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement]) }}" class="btn btn-sm uppercase"
                disabled>edit</a>
        </div>

        <form action="{{ route('admin.requirements.update', ['requirement' => $requirement->id]) }}" method="post" class="grid grid-cols-3 gap-4">
            @csrf
            @method('PUT')
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">Name</legend>
                <input value="{{ $requirement->name }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('name')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">description</legend>
                <input value="{{ $requirement->description }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('description')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">due</legend>
                <input value="{{ \Carbon\Carbon::parse($requirement->due)->format('Y-m-d') }}" type="date"
                    class="input w-full" placeholder="---" readonly />
                @error('due')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">target</legend>
                <input value="{{ $requirement->target }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('target')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">target id</legend>
                <input value="{{ $requirement->target_id }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('target_id')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">status</legend>
                <input value="{{ $requirement->status }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('status')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">priority</legend>
                <input value="{{ $requirement->priority }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('priority')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">created by</legend>
                <input value="{{ $requirement->created_by }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('created_by')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">updated by</legend>
                <input value="{{ $requirement->updated_by }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('updated_by')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">archived by</legend>
                <input value="{{ $requirement->archived_by }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('archived_by')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">created at</legend>
                <input value="{{ $requirement->created_at }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('created_at')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">updated at</legend>
                <input value="{{ $requirement->updated_at }}" type="text" class="input w-full" placeholder="---"
                    readonly />
                @error('updated_at')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </fieldset>
            <div class="col-span-3 text-end">
                <button type="submit" class="btn btn-sm btn-success uppercase">confirm</button>
            </div>
        </form>
    </div>
</x-admin.app-layout>
