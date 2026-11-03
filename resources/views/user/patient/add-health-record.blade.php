{{-- This file is a partial included in health-tracking.blade.php for the "Add New Record" modal. --}}

<h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="addHealthRecordModalLabel">{{ __('Add New Health Record') }}</h3>

<form method="POST" action="{{ route('add.health.record') }}" class="mt-4">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Date') }}</label>
            <input type="date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" id="date" name="date" required>
        </div>
        <div>
            <label for="blood_pressure" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Blood Pressure</label>
            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" id="blood_pressure" name="blood_pressure" placeholder="e.g., 120/80">
        </div>
        <div>
            <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Weight</label>
            <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" id="weight" name="weight" step="0.01" placeholder="in kg">
        </div>
        <div>
            <label for="temperature" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Temperature</label>
            <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" id="temperature" name="temperature" step="0.01" placeholder="in °C or °F">
        </div>
        <div>
            <label for="glucose_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Glucose Level</label>
            <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" id="glucose_level" name="glucose_level" step="0.01" placeholder="e.g., 90 mg/dL">
        </div>
    </div>
    <div class="mt-6 flex justify-end space-x-3">
        <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 focus:outline-none">
            {{ __('Cancel') }}
        </button>
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none">
            {{ __('Save Record') }}
        </button>
    </div>
</form>