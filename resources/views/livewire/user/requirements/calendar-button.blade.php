<div class="relative" x-data="{
    showCalendar: false,
    calendar: null,
    isInitialized: false,
    
    init() {
        // Setup Livewire event listener first
        Livewire.on('requirementsUpdated', () => {
            this.updateCalendarEvents();
        });

        // Watch for calendar visibility changes
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
                const event = info.event;
                alert(`Requirement: ${event.title}\nDue: ${event.startStr}`);
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
    <button 
        @click="showCalendar = !showCalendar"
    class="flex items-center gap-2 px-4 py-2 bg-white/90 hover:bg-white text-[#1B512D] border border-white/20 hover:border-white/40 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 backdrop-blur-sm"
>
    <i class="fa-regular fa-calendar text-[#1C7C54]"></i>
    <span class="font-medium text-sm">Calendar</span>
    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" 
       :class="showCalendar ? 'rotate-180' : ''"></i>
</button>

    <div 
        x-show="showCalendar"
        x-transition.opacity.duration.300ms
        class="absolute right-0 mt-2 w-[600px] bg-white rounded-lg shadow-lg z-10 p-4"
        style="max-height: 80vh;"
        x-cloak
        x-on:click.outside="showCalendar = false"
        wire:ignore.self
    >
        <div class="h-full flex flex-col">
            <div x-ref="calendar" class="flex-grow min-h-[400px] fc"></div>
        </div>
    </div>
</div>