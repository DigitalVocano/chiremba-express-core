{{-- resources/views/user/patient/partials/appointment-summary-card.blade.php --}}

<div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
    <div class="flex-shrink-0">
        <div class="text-tele-dark-blue text-3xl">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
    <div class="flex-grow ml-4 text-right">
        <div class="text-2xl font-bold text-tele-dark-blue">{{ $count }}</div>
        <div class="text-sm text-gray-600">{{ $title }}</div>
    </div>
</div>