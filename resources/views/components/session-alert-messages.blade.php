@php
    $alerts = [
        'success' => session('success'),
        'error' => session('error'),
        'info' => session('info'),
        'warning' => session('warning'),
    ];
    $icons = [
        'success' => 'circle-check',
        'error' => 'circle-xmark',
        'info' => 'circle-info',
        'warning' => 'triangle-exclamation',
    ];
@endphp

@foreach ($alerts as $type => $messages)
    @if ($messages)
        <div class="toast toast-top toast-end z-50">
            @foreach ((array) $messages as $message)
                <div role="alert" class="alert alert-{{ $type }} flex items-center gap-2 relative">
                    <i class="fa-regular fa-{{ $icons[$type] }}"></i>
                    <span>{{ $message }}</span>
                    <button type="button" class="close-toast text-gray-500 hover:text-gray-700"><i
                            class="fa-regular fa-circle-xmark"></i></button>
                </div>
            @endforeach
        </div>
    @endif
@endforeach

<script>
    // Auto-hide toast after 3 seconds
    document.querySelectorAll('.toast').forEach(function(toast) {
        setTimeout(() => toast.style.display = 'none', 5000);
    });

    // Close button handler
    document.querySelectorAll('.close-toast').forEach(function(btn) {
        btn.addEventListener('click', function() {
            btn.closest('.toast').style.display = 'none';
        });
    });
</script>
