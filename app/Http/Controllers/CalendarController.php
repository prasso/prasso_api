<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    /**
     * Get all events from the Google Calendar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $events = Event::get();
            
            $formattedEvents = $events->map(function ($event) {
                // Log the raw event data for debugging
                Log::info('Raw event data:', [
                    'summary' => $event->summary,
                    'start' => $event->startDateTime,
                    'end' => $event->endDateTime,
                    'start_date' => $event->start?->date,
                    'end_date' => $event->end?->date,
                    'is_all_day' => $event->isAllDayEvent(),
                ]);

                return [
                    'id' => $event->id,
                    'summary' => $event->summary,
                    'description' => $event->description,
                    'start' => $event->start?->date ?? $event->startDateTime,
                    'end' => $event->end?->date ?? $event->endDateTime,
                    'location' => $event->location,
                    'status' => $event->status,
                    'is_all_day' => $event->isAllDayEvent(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedEvents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch calendar events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the calendar view
     *
     * @return \Illuminate\View\View
     */
    public function view()
    {
        return view('calendar.index');
    }
}
