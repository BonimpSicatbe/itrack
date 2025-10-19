<div class="rounded-xl shadow-sm px-6 py-4" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
    @if(auth()->user()->is_active)
        {{-- User is active: Show semester information --}}
        @if($currentSemester)
            <div class="flex items-center justify-between">
                {{-- Left side: Semester info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex items-center justify-center w-10 h-10 bg-green-50 rounded-xl">
                            <i class="fas fa-calendar-alt text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white text-lg truncate">
                                {{ $currentSemester->name }}
                            </h3>
                            <p class="text-sm text-gray-300">
                                {{ $currentSemester->start_date->format('M d') }} - {{ $currentSemester->end_date->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Right side: Notification icon only --}}
                <div class="flex items-center" wire:ignore>
                    @livewire('user.dashboard.notification')
                </div>
            </div>

            {{-- Status alert (only if needed) --}}
            @if($daysRemaining <= 7 && $daysRemaining > 0)
                <div class="mt-3 flex items-center gap-2 p-2 bg-orange-100 rounded-xl border-l-4 border-orange-400">
                    <i class="fas fa-exclamation-triangle text-orange-500 text-xs"></i>
                    <span class="text-xs text-orange-700 font-medium">
                        Semester ending soon
                    </span>
                </div>
            @elseif($daysRemaining <= 0)
                <div class="mt-3 flex items-center gap-2 p-2 bg-red-100 rounded-xl border-l-4 border-red-400">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
                    <span class="text-xs text-red-700 font-medium">
                        Semester has ended
                    </span>
                </div>
            @endif
        @else
            {{-- No Active Semester - Compact version --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center">
                        <i class="fas fa-triangle-exclamation text-amber-400 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">No Active Semester</h3>
                        <p class="text-sm text-gray-100">Progress, pending, and recent data will be displayed here once a semester is active.</p>
                    </div>
                </div>
                
                {{-- Notification icon even when no active semester --}}
                <div class="flex items-center">
                    @livewire('user.dashboard.notification')
                </div>
            </div>
        @endif
    @else
        {{-- User is not active --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center">
                    <i class="fas fa-user-slash text-amber-400 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Account Deactivated</h3>
                    <p class="text-sm text-gray-100">Your account is currently deactivated. Please contact the administrator to reactivate your account.</p>
                </div>
            </div>
            
            {{-- Notification icon --}}
            <div class="flex items-center">
                @livewire('user.dashboard.notification')
            </div>
        </div>
    @endif
</div>