<div>
    @if($show)
        <div class="fixed top-4 right-4 z-50 w-80 space-y-3" style="display: flex; flex-direction: column; align-items: flex-end;">
            @foreach ($messages as $index => $message)
                <div 
                    x-data="{ show: true }" 
                    x-init="setTimeout(() => { show = false; $wire.removeMessage('{{ $message['id'] }}') }, {{ $message['duration'] }});"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="transform opacity-0 translate-y-2"
                    x-transition:enter-end="transform opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="transform opacity-100 translate-y-0"
                    x-transition:leave-end="transform opacity-0 translate-y-2"
                    class="w-full"
                    style="order: {{ $index }}; margin-top: {{ $index * 0.5 }}rem;"
                >
                    <div class="alert shadow-lg flex items-center justify-between
                        @if($message['type'] === 'success') alert-success 
                        @elseif($message['type'] === 'warning') alert-warning 
                        @elseif($message['type'] === 'error') alert-error
                        @else alert-info @endif">
                        <!-- Content on the left -->
                        <div class="flex items-center flex-1 min-w-0"> <!-- Added min-w-0 to prevent overflow -->
                            @if($message['type'] === 'success')
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                    class="stroke-current flex-shrink-0 h-6 w-6" 
                                    fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif($message['type'] === 'warning')
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @elseif($message['type'] === 'error')
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                            <span class="ml-2 truncate">{{ $message['content'] }}</span>
                        </div>
                        
                        <!-- Close button on the far right -->
                        <button class="flex-shrink-0 ml-3 text-current hover:opacity-70 transition-opacity" 
                                @click="show = false; $wire.removeMessage('{{ $message['id'] }}')">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>