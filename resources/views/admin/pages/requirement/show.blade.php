<x-admin.app-layout>
    @livewire('admin.dashboard.overview')

    <div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
        <div class="flex flex-row gap-4 justify-between items-center w-full">
            <div class="text-lg font-bold uppercase">Requirement</div>
            <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement]) }}"
                class="btn btn-sm uppercase">edit</a>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">Name</legend>
                <input value="{{ $requirement->name }}" type="text" class="input w-full" placeholder="---" readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">description</legend>
                {{-- <textarea class="textarea w-full" placeholder="Bio">{{$requirement->description}}</textarea> --}}
                <input value="{{ $requirement->description }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">due</legend>
                {{-- <textarea class="textarea w-full" placeholder="Bio">{{$requirement->due}}</textarea> --}}
                <input value="{{ \Carbon\Carbon::parse($requirement->due)->format('Y-m-d') }}" type="date"
                    class="input w-full" placeholder="---" readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">target</legend>
                <input value="{{ $requirement->target }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">target id</legend>
                <input value="{{ $requirement->target_id }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">status</legend>
                <input value="{{ $requirement->status }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">priority</legend>
                <input value="{{ $requirement->priority }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">created by</legend>
                <input value="{{ $requirement->created_by }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">updated by</legend>
                <input value="{{ $requirement->updated_by }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">archived by</legend>
                <input value="{{ $requirement->archived_by }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">created at</legend>
                <input value="{{ $requirement->created_at }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend w-full capitalize">updated at</legend>
                <input value="{{ $requirement->updated_at }}" type="text" class="input w-full" placeholder="---"
                    readonly />
            </fieldset>
        </div>
    </div>
</x-admin.app-layout>
