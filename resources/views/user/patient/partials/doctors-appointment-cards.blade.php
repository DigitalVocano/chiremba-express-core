<div class="grid grid-cols-1 gap-4 md:hidden">
    @forelse($bookdata as $am)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 space-y-3">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $am->doctorls ? $am->doctorls->name : 'Unknown Doctor' }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ date("F d,Y",strtotime($am->date)) }}, {{ $am->slot_name }}</p>
                </div>
                <button onclick="get_desc({{ $am->id }})" class="text-primary-600 hover:text-primary-600 text-xl" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <p><i class="fas fa-envelope mr-2"></i>{{ $am->doctorls ? $am->doctorls->email : 'N/A' }}</p>
                <p><i class="fas fa-phone mr-2"></i>{{ $am->phone }}</p>
                <p><i class="fas fa-sticky-note mr-2"></i>{{ Str::limit($am->user_description, 70) }}</p>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700">
                @if($am->status == '1') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100">Received</span>
                @elseif($am->status == '2') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Approved</span>
                @elseif($am->status == '3') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100">In Process</span>
                @elseif($am->status == '4') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Completed</span>
                @elseif($am->status == '5') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">Rejected</span>
                @else <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300">Absent</span>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            No appointments found.
            <div class="mt-4">
                <a href="{{ url('searchdoctor') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Book Appointment <i class="fas fa-arrow-right ml-2"></i></a>
            </div>
        </div>
    @endforelse
    <div class="mt-4">
        {{ $bookdata->links('pagination::tailwind') }}
    </div>
</div>
