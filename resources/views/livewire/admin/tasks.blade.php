<div wire:poll..500ms class="flex flex-col gap-4 p-4 bg-white rounded-lg shadow-md">
    {{-- task header --}}
    <div class="text-xl font-bold">Task Summary</div>

    {{-- tasks body --}}
    <div class="flex flex-col gap-2">
        {{-- Filters, search, and add button --}}
        <div class="flex flex-row gap-2 w-full items-center">
            @for ($i = 0; $i < 3; $i++)
                <div class="dropdown">
                    <div tabindex="0" role="button" class="btn btn-sm btn-default">Filter</div>
                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm">
                        <li><a>Item 1</a></li>
                        <li><a>Item 2</a></li>
                    </ul>
                </div>
            @endfor

            <div class="flex-1"></div>

            <input type="text" name="searchTask" id="searchTask"
                class="input input-bordered input-sm md:w-1/2 sm:w-full" placeholder="Search tasks..." />

            {{-- Trigger modal --}}
            {{-- <label for="createNewTask" class="btn btn-sm btn-default">Create New Task</label> --}}
            <label for='createNewTask' class="btn btn-sm btn-default">Create New Task</label>
        </div>

        {{-- Task Table --}}
        <div class="overflow-auto max-h-[500px]">
            <table class="table table-sm table-auto table-zebra w-full">
                <thead>
                    <tr>
                        <th class="capitalize">name</th>
                        <th class="capitalize">description</th>
                        <th class="capitalize">status</th>
                        <th class="capitalize">created by</th>
                        <th class="capitalize">updated by</th>
                        <th class="capitalize">actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Check if tasks are empty --}}
                    @if ($tasks->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">No tasks found.</td>
                        </tr>
                    @else
                        @foreach ($tasks as $task)
                            <tr>
                                <td>{{ $task->name }}</td>
                                <td>{{ $task->description }}</td>
                                <td>{{ $task->status }}</td>
                                <td>{{ $task->created_by }}</td>
                                <td>{{ $task->updated_by }}</td>
                                <td>
                                    <button class="btn btn-xs btn-primary">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <div class="text-xs text-gray-500">Total Task: {{ $tasks->count() }}</div>
    </div>

    @include('admin.partials.createNewTaskModal')
</div>
