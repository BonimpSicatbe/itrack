<div class="relative" 
     x-data="{
        showCalendar: false,
        calendar: null,
        isInitialized: false,
        selectedEvent: null,
        
        init() {
            Livewire.on('requirementsUpdated', () => this.updateCalendarEvents());

            this.$watch('showCalendar', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        if (!this.isInitialized) {
                            this.initCalendar();
                            this.isInitialized = true;
                        } else {
                            this.calendar.render();
                            setTimeout(() => this.calendar.updateSize(), 100);
                        }
                    });
                }
            });
        },
        
        initCalendar() {
            this.calendar = new FullCalendar.Calendar(this.$refs.calendar, {
                plugins: [FullCalendar.dayGridPlugin, FullCalendar.interactionPlugin],
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                events: @js($requirements),
                eventClick: (info) => {
                    info.jsEvent.preventDefault();
                    this.selectedEvent = info.event;
                    this.$dispatch('open-modal', 'event-details');
                },
                eventClassNames(arg) {
                    let classes = ['cursor-pointer', 'text-xs', 'font-semibold'];

                    // color-code events based on status
                    switch (arg.event.extendedProps.status) {
                        case 'overdue':
                            classes.push('bg-red-100', 'text-red-800', 'border-l-4', 'border-red-500');
                            break;
                        case 'due_soon':
                            classes.push('bg-amber-100', 'text-amber-800', 'border-l-4', 'border-amber-500');
                            break;
                        default:
                            classes.push('bg-emerald-100', 'text-emerald-800', 'border-l-4', 'border-emerald-500');
                    }

                    return classes;
                }
            });
            
            this.calendar.render();
            setTimeout(() => this.calendar.updateSize(), 100);
        },
        
        updateCalendarEvents() {
            if (!this.calendar) return;
            this.calendar.removeAllEvents();
            this.calendar.addEventSource(@js($requirements));
            setTimeout(() => {
                this.calendar.refetchEvents();
                this.calendar.updateSize();
            }, 100);
        }
     }">

    <!-- Modern Toggle Button -->
    <button 
        @click="showCalendar = !showCalendar"
        class="relative flex items-center gap-2 px-5 py-3 bg-white text-gray-800 rounded-xl font-semibold shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 group"
    >
        <!-- Animated Indicator -->
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 group-hover:opacity-100"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        
        <span class="text-sm font-semibold text-gray-700 group-hover:text-emerald-700 transition-colors">Academic Calendar</span>
        
        <svg class="w-4 h-4 text-gray-500 transition-transform duration-300 group-hover:text-emerald-600" 
             :class="showCalendar && 'rotate-180'" 
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Calendar Dropdown - Modern Glass Morphism Design -->
    <div 
        x-show="showCalendar"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        x-on:click.outside="showCalendar = false"
        wire:ignore.self
        class="absolute right-0 mt-3 w-[720px] bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl z-10 border border-white/20 overflow-hidden max-h-[60vh]"
    >
        <!-- Calendar Container -->
        <div class="p-5">
            <div class="relative bg-white rounded-xl shadow-inner border border-gray-100 overflow-hidden">
                <div x-ref="calendar" class="min-h-[500px] p-4"></div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal - Modern Design -->
    <x-modal name="event-details" :show="false" maxWidth="md">
        <div class="p-0 overflow-hidden rounded-lg">
            <!-- Modal Header with Status-Based Color -->
            <div class="p-5 text-white" 
                 :class="{
                    'bg-red-500': selectedEvent?.extendedProps.status === 'overdue',
                    'bg-amber-500': selectedEvent?.extendedProps.status === 'due_soon',
                    'bg-emerald-500': !selectedEvent || (selectedEvent?.extendedProps.status !== 'overdue' && selectedEvent?.extendedProps.status !== 'due_soon')
                 }">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold" x-text="selectedEvent ? selectedEvent.title : ''"></h3>
                    <button @click="$dispatch('close-modal', 'event-details')" class="text-white/80 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center mt-2">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span x-text="selectedEvent?.startStr"></span>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="p-5 bg-white space-y-4">
                <div class="flex items-center p-3 rounded-lg bg-gray-50">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 mr-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium capitalize" 
                           x-text="selectedEvent?.extendedProps.status.replace('_', ' ')"></p>
                    </div>
                </div>
                
                <div class="flex items-center p-3 rounded-lg bg-gray-50">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600 mr-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Priority</p>
                        <p class="font-medium capitalize" x-text="selectedEvent?.extendedProps.priority"></p>
                    </div>
                </div>
                
                <div x-show="selectedEvent?.extendedProps.isOverdue" class="flex items-center p-3 rounded-lg bg-red-50 text-red-800">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-semibold">This requirement is overdue</span>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-5 py-4 bg-gray-50 flex justify-end">
                <button @click="$dispatch('close-modal', 'event-details')" 
                        class="px-5 py-2 bg-gray-800 text-white rounded-lg font-medium hover:bg-gray-900 transition-colors">
                    Close Details
                </button>
            </div>
        </div>
    </x-modal>

    <!-- FullCalendar Custom Styles -->
    <style>
        .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #111827 !important;
        }
        
        .fc-button {
            background: transparent !important;
            color: #374151 !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 8px !important;
            padding: 0.4rem 0.8rem !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }
        
        .fc-button:hover {
            background: #f9fafb !important;
            color: #111827 !important;
        }
        
        .fc-button-primary:not(:disabled).fc-button-active {
            background: #f3f4f6 !important;
            color: #111827 !important;
        }
        
        .fc-daygrid-day {
            background: #fff !important;
            border: 1px solid #f3f4f6 !important;
        }
        
        .fc-day-today {
            background: rgba(16, 185, 129, 0.05) !important;
        }
        
        .fc-event {
            border: none !important;
            border-radius: 6px !important;
            padding: 4px 6px !important;
            margin-bottom: 4px !important;
            font-size: 0.75rem !important;
        }
        
        .fc-daygrid-day-number {
            color: #374151;
            font-weight: 500;
            padding: 8px !important;
        }
        
        .fc-col-header-cell {
            background: #f9fafb;
        }
        
        .fc-col-header-cell-cushion {
            color: #374151;
            font-weight: 600;
            padding: 8px !important;
        }
    </style>
</div>