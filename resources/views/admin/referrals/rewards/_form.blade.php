<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Reward Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $reward->name ?? '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
    </div>
    <div>
        <label for="points_required" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Points Required</label>
        <input type="number" name="points_required" id="points_required" value="{{ old('points_required', $reward->points_required ?? '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
    </div>
    <div class="col-span-full">
        <label for="description" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Description</label>
        <textarea name="description" id="description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('description', $reward->description ?? '') }}</textarea>
    </div>
    <div>
        <label for="type" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Reward Type</label>
        <select name="type" id="type" x-model="type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="wallet">Wallet Credit</option>
            <option value="custom">Custom (e.g., Gadget, Service)</option>
        </select>
    </div>
    <div x-show="type === 'wallet'">
        <label for="value" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Wallet Credit Value ($)</label>
        <input type="number" step="0.01" name="value" id="value" value="{{ old('value', $reward->value ?? '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    </div>
    <div class="col-span-full">
        <label class="inline-flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm" {{ old('is_active', $reward->is_active ?? 1) == 1 ? 'checked' : '' }}>
            <span class="ml-2 text-gray-700 dark:text-gray-300">Active</span>
        </label>
    </div>
</div>
