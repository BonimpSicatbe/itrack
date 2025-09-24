<x-mail::message>
    # New Requirement Assigned

    Hello {{ $user->name }},

    You have been assigned to a new requirement. Here are the details:

    **Title:** {{ $requirement->title }}

    **Description:** {{ $requirement->description }}

    **Due Date:** {{ optional($requirement->due_date)->format('F j, Y') ?? 'N/A' }}

    <x-mail::button :url="route('user.dashboard')">
        View Requirement
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
