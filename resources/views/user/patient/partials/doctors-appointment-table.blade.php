<div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
    <h3 class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200">Your Appointments</h3>
    <div class="w-full sm:w-auto flex-shrink-0">
        <select onchange="window.location.href = this.value" class="block w-full mt-1 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:text-gray-200">
            <option value="{{ url('/userdashboard', ['type' => 1]) }}" {{ $type == 1 ? 'selected' : '' }}>Today</option>
            <option value="{{ url('/userdashboard', ['type' => 3]) }}" {{ $type == 3 ? 'selected' : '' }}>Upcoming</option>
            <option value="{{ url('/userdashboard', ['type' => 2]) }}" {{ $type == 2 ? 'selected' : '' }}>Past</option>
        </select>
    </div>
</div>

<div class="overflow-x-auto hidden md:block"> {{-- This table is hidden on mobile, shown on desktop --}}
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Doctor Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Slot</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($bookdata as $am)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $am->doctorls ? $am->doctorls->name : 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ date("F d,Y",strtotime($am->date)) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $am->slot_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $am->doctorls ? $am->doctorls->email : 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $am->phone }}</td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $am->user_description }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    @if($am->status == '1') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100">Received</span>
                    @elseif($am->status == '2') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Approved</span>
                    @elseif($am->status == '3') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100">In Process</span>
                    @elseif($am->status == '4') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Completed</span>
                    @elseif($am->status == '5') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">Rejected</span>
                    @else <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300">Absent</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="get_desc({{ $am->id }})" class="text-primary-600 hover:text-primary-600" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                    No appointments found.
                    <div class="mt-4">
                        <a href="{{ url('searchdoctor') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Book Appointment <i class="fas fa-arrow-right ml-2"></i></a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $bookdata->links('pagination::tailwind') }} {{-- Using Tailwind pagination view --}}
</div>
