<x-app-layout :site="$site ?? null">
    <x-slot name="title">Calendar Events</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Calendar Events') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <input type="text" 
                               id="eventFilter" 
                               placeholder="Filter by title..." 
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               onkeyup="filterEvents()">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(0)">
                                        Title
                                        <span class="ml-1">↕</span>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(2)">
                                        Start Time
                                        <span class="ml-1">↕</span>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        End Time
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="eventsTable" class="bg-white divide-y divide-gray-200">
                                <!-- Events will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    let events = [];
    let sortDirection = 1;
    
    function formatDate(dateString, isAllDay = false) {
        // Handle date-only format (YYYY-MM-DD)
        if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const [year, month, day] = dateString.split('-');
            const date = new Date(year, month - 1, day);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        // Handle datetime format
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            console.error('Invalid date:', dateString);
            return 'Invalid date';
        }

        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function formatEventDates(event) {
        if (event.is_all_day) {
            const startDate = formatDate(event.start, true);
            const endDate = formatDate(event.end, true);
            return startDate === endDate ? startDate : `${startDate} – ${endDate}`;
        }
        return `${formatDate(event.start)} - ${formatDate(event.end)}`;
    }

    function filterEvents() {
        const filter = document.getElementById('eventFilter').value.toLowerCase();
        const tbody = document.getElementById('eventsTable');
        const rows = tbody.getElementsByTagName('tr');

        for (let row of rows) {
            const titleCell = row.getElementsByTagName('td')[0];
            if (titleCell) {
                const titleText = titleCell.textContent || titleCell.innerText;
                row.style.display = titleText.toLowerCase().includes(filter) ? '' : 'none';
            }
        }
    }

    function sortTable(columnIndex) {
        sortDirection *= -1;

        events.sort((a, b) => {
            let valueA, valueB;
            
            if (columnIndex === 0) {
                valueA = (a.summary || '').toLowerCase();
                valueB = (b.summary || '').toLowerCase();
            } else if (columnIndex === 2) {
                // Parse dates considering the format
                valueA = a.is_all_day ? new Date(a.start.split('T')[0]) : new Date(a.start);
                valueB = b.is_all_day ? new Date(b.start.split('T')[0]) : new Date(b.start);
                valueA = valueA.getTime();
                valueB = valueB.getTime();
            }
            
            return sortDirection * (valueA < valueB ? -1 : (valueA > valueB ? 1 : 0));
        });

        renderEvents();
    }

    function renderEvents() {
        const tbody = document.getElementById('eventsTable');
        tbody.innerHTML = '';
        
        events.forEach(event => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${event.summary || ''}</td>
                <td class="px-6 py-4 text-sm text-gray-500">${event.description || 'No description'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatEventDates(event)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 ${event.is_all_day ? 'hidden' : ''}">${event.is_all_day ? '' : formatDate(event.end)}</td>
            `;
            tbody.appendChild(row);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetch('/api/calendar/events')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    events = data.data;
                    console.log('Events data:', events); // Debug log
                    renderEvents();
                } else {
                    console.error('Failed to load events:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
    </script>
    @endpush
</x-app-layout>
