import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import multiMonthPlugin from '@fullcalendar/multimonth';
import timeGridPlugin from '@fullcalendar/timegrid';
import allLocales from '@fullcalendar/core/locales-all';

window.FullCalendar = {
  Calendar,
  dayGridPlugin,
  interactionPlugin,
  listPlugin,
  multiMonthPlugin,
  timeGridPlugin,
  globalLocales: allLocales,
};
