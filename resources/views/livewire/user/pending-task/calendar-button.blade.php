<div class="relative" x-data="{
    showCalendar: false,
    init() {
        // Watch for Livewire updates
        Livewire.on('requirementsUpdated', () => {
            if (this.calendar) {
                this.calendar.refetchEvents();
            }
        });
    },
    initCalendar() {
        if (this.showCalendar && !this.calendar) {
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
        }
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
        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-10 p-4"
        x-on:click.outside="showCalendar = false"
        wire:ignore
    >
        <div x-ref="calendar"></div>
    </div>
</div>