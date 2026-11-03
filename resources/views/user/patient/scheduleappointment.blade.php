@extends('layouts.patient')
@section('title')
    {{__('Appointment Calendar')}}
@stop
@section('meta-data')
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{__('KumbaCare')}}"/>
    <meta property="og:title" content="{{__('Appointment Calendar')}}"/>
    <meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
    <meta property="og:image:width" content="250px"/>
    <meta property="og:image:height" content="250px"/>
    <meta property="og:site_name" content="{{__('KumbaCare')}}"/>
    <meta property="og:description" content="{{__('meta_description')}}"/>
    <meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
    <link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
    {{-- <meta name="viewport" content="width=device-width, initial-scale=1"> --}}
@stop

@section('header_styles')
    {{-- FullCalendar CSS --}}
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <style>
        /* Custom FullCalendar styles if needed, or Tailwind overrides */
        .fc-event {
            cursor: pointer;
            border-color: #3b82f6 !important; /* primary-500 */
            background-color: #60a5fa !important; /* lighter blue */
        }
        .dark .fc-event {
            background-color: #1d4ed8 !important; /* darker blue for dark mode */
            border-color: #1e40af !important;
        }
        .dark .fc-daygrid-day-number, .dark .fc-col-header-cell-cushion {
            color: #d1d5db; /* gray-300 */
        }
        .dark .fc-day-today {
            background-color: #374151 !important; /* gray-700 */
        }
        .dark .fc-toolbar-title {
            color: #e5e7eb; /* gray-200 */
        }
        .dark .fc-button {
            background-color: #4b5563 !important; /* gray-600 */
            border-color: #374151 !important; /* gray-700 */
            color: #e5e7eb !important;
        }
        .dark .fc-button-primary:not(:disabled).fc-button-active, .dark .fc-button-primary:not(:disabled):active {
            background-color: #3b82f6 !important;
            border-color: #1d4ed8 !important;
        }
    </style>
@stop

@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Appointment Calendar')}}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 sm:p-6">
        {{-- FullCalendar will render here --}}
        <div class="response alert alert-success mt-2" style="display: none;"></div> {{-- Keep this if used by your calendar JS --}}
        <div id='calendar'></div>
    </div>
@stop

@section('footer_scripts')
    {{-- FullCalendar JS --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
          },
          events: "{{ url('getuserappointment') }}", // URL to fetch events
          eventClick: function(info) {
            // When an event is clicked, you can show details or redirect
            // For example, open the doctor details modal (if you have one)
            // Or redirect to an appointment details page:
            // window.location.href = "{{ url('user/appointment-details') }}/" + info.event.id;
            alert('Event: ' + info.event.title + '\nTime: ' + info.event.start.toLocaleTimeString());
            // You might want to use a modal here instead of an alert
          },
          // Add more FullCalendar options as needed
          // Example: dateClick, eventDidMount, etc.
        });
        calendar.render();
      });
    </script>
@stop
