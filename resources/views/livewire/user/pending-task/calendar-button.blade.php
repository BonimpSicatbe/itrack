<div class="relative" x-data="{
    showCalendar: false,
    calendar: null,
    init() {
        // Initialize only when fully shown
        this.$watch('showCalendar', (value) => {
            if (value && !this.calendar) {
                this.$nextTick(() => {
                    this.initCalendar();
                });
            }
        });
    },
    initCalendar() {
        this.calendar = new FullCalendar.Calendar(this.$refs.calendar, {
            plugins: [FullCalendar.dayGridPlugin],
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
        // Force resize after render
        setTimeout(() => this.calendar.updateSize(), 100);
    }
}">

    <button 
        @click="showCalendar = !showCalendar; initCalendar()"
        class="btn btn-sm btn-ghost gap-2"
    >
        <i class="fa-regular fa-calendar"></i>
        <span>Calendar</span>
    </button>

    <div 
        x-show="showCalendar"
        x-transition
        class="absolute right-0 mt-2 w-[600px] bg-white rounded-lg shadow-lg z-10 p-4 overflow-hidden"
        x-on:click.outside="showCalendar = false"
        wire:ignore
        style="max-height: 80vh;"
    >
        <div class="h-full flex flex-col">
            <div x-ref="calendar" class="flex-grow min-h-0"></div>
        </div>
    </div>
</div>