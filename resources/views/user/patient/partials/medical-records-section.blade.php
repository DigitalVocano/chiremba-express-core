{{--
    This partial displays a list of medical records for a specific type.
    It expects two variables:
    - $title: The title of the section (e.g., 'E-Prescriptions').
    - $records: A collection of medical records to display.
--}}

<div class="space-y-4">
    @if($records->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <i class="fas fa-file-medical-alt text-4xl mb-3"></i>
            <p>No {{ strtolower($title) }} found.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($records as $record)
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg shadow p-4 flex flex-col justify-between transition-shadow hover:shadow-md">
                    <div>
                        <div class="flex items-center mb-2">
                            @php
                                $icon = 'fa-file-alt'; // default
                                if ($record->record_type == 'e-prescription' || $record->record_type == 'pdf') {
                                    $icon = 'fa-file-pdf text-red-500';
                                } elseif ($record->record_type == 'image') {
                                    $icon = 'fa-file-image text-blue-500';
                                } elseif ($record->record_type == 'appointment') {
                                    $icon = 'fa-file-invoice text-green-500';
                                }
                            @endphp
                            <i class="fas {{ $icon }} text-xl mr-3"></i>
                            <h5 class="font-semibold text-gray-900 dark:text-gray-100 truncate" title="{{ $record->document_name }}">
                                {{ Str::limit($record->document_name, 25) }}
                            </h5>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                            Uploaded on: {{ \Carbon\Carbon::parse($record->created_at)->format('M d, Y') }}
                        </p>
                    </div>
                    <div class="mt-auto pt-3 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between space-x-2">
                        <form action="{{ route('update.medical.record.visibility', $record->id) }}" method="POST" class="flex items-center">
                            @csrf
                            @method('PUT')
                            <select name="visibility" onchange="this.form.submit()" class="text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-600 focus:ring-primary-600 rounded-md shadow-sm py-1">
                                <option value="visible" {{ $record->visibility == 'visible' ? 'selected' : '' }}>Visible</option>
                                <option value="hidden" {{ $record->visibility == 'hidden' ? 'selected' : '' }}>Hidden</option>
                            </select>
                        </form>
                        <div class="flex items-center space-x-1">
                            <a href="{{ route('view.medical.record', $record->id) }}" class="p-2 text-gray-500 dark:text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ asset($record->document_path) }}" download class="p-2 text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-green-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600" title="Download"><i class="fas fa-download"></i></a>
                            <form action="{{ route('delete.medical.record', $record->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');"> @csrf @method('DELETE') <button type="submit" class="p-2 text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600" title="Delete"><i class="fas fa-trash"></i></button></form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>