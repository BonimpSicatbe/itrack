import './bootstrap';
import '../css/app.css';

// Alpine.js initialization
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// FullCalendar imports - use static imports for reliability
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

// Make available globally
window.FullCalendar = { Calendar, dayGridPlugin, interactionPlugin };

// Initialize any existing calendars on page load
document.addEventListener('DOMContentLoaded', () => {
    const calendarEls = document.querySelectorAll('[data-calendar]');
    calendarEls.forEach(el => {
        const events = JSON.parse(el.dataset.events || '[]');
        new window.FullCalendar.Calendar(el, {
            plugins: [window.FullCalendar.dayGridPlugin],
            initialView: 'dayGridMonth',
            events
        }).render();
    });
});
