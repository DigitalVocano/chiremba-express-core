{{-- This partial view renders the translation table for a specific language with inline editing --}}
<div x-data="{ searchTerm: '' }">
    <!-- Search Input -->
    <div class="mb-4">
        <input type="text" x-model.debounce.300ms="searchTerm" placeholder="Search by key or value..."
               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">{{ __('Key') }}</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">{{ __('Value') }}</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse (collect($messages)->sortKeys() as $key => $value)
                    <tr x-data="{ editing: false, newValue: '{{ e(addslashes($value)) }}', originalValue: '{{ e(addslashes($value)) }}' }"
                        x-show="searchTerm === '' || '{{ strtolower($key) }}'.includes(searchTerm.toLowerCase()) || '{{ strtolower(e(addslashes($value))) }}'.includes(searchTerm.toLowerCase())"
                        :class="{ 'bg-yellow-50 dark:bg-gray-700/50': editing }">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $key }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300" @dblclick="editing = true; $nextTick(() => $refs.editInput.focus())">
                            <span x-show="!editing" x-text="newValue || '...'" class="cursor-pointer"></span>
                            <input type="text" x-show="editing" x-ref="editInput" x-model="newValue"
                                   @keydown.enter.prevent="$dispatch('update-translation', { url: '{{ route('languages.update', ['lang' => $lang, 'key' => $key]) }}', key: '{{ $key }}', value: newValue, token: '{{ csrf_token() }}' }).then(s => { if(s) { editing = false; originalValue = newValue; } })"
                                   @keydown.escape.prevent="editing = false; newValue = originalValue"
                                   class="w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div x-show="!editing">
                                <button @click="editing = true; $nextTick(() => $refs.editInput.focus())" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200 mr-3">{{ __('Edit') }}</button>
                                <form action="{{ route('languages.destroy', ['lang' => $lang, 'key' => $key]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this key from ALL languages? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">{{ __('Delete') }}</button>
                                </form>
                            </div>
                            <div x-show="editing" class="flex items-center justify-end space-x-2">
                                <button @click="$dispatch('update-translation', { url: '{{ route('languages.update', ['lang' => $lang, 'key' => $key]) }}', key: '{{ $key }}', value: newValue, token: '{{ csrf_token() }}' }).then(s => { if(s) { editing = false; originalValue = newValue; } })" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200">{{ __('Save') }}</button>
                                <button @click="editing = false; newValue = originalValue" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">{{ __('Cancel') }}</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">{{ __('No translations found for this language.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>