<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-2xl h-full flex flex-col">
    <div class="p-4 bg-primary-50 dark:bg-primary-900/20 text-center">
        <div class="relative w-32 h-32 mx-auto">
            @php
                $initials = \App\Helpers\Helper::getInitials($pharmacy->name ?? 'Pharmacy');
                $hasCustomImage = !empty($pharmacy->image) && !in_array($pharmacy->image, ['default.png', 'user.png', 'profile.png', 'defaultpharmacy.png', 'null']);
                $imagePath = $hasCustomImage ? asset('upload/doctors/' . $pharmacy->image) : '';
            @endphp
            @if($hasCustomImage)
                <img class="rounded-full object-cover w-full h-full border-4 border-white dark:border-gray-700 shadow-md" 
                     src="{{ $imagePath }}" 
                     alt="{{ $pharmacy->name }}"
                     onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
            @endif
            <div class="rounded-full w-full h-full border-4 border-white dark:border-gray-700 shadow-md flex items-center justify-center text-white text-2xl font-extrabold tracking-wider bg-gradient-to-br from-primary-600 via-primary-500 to-emerald-600 select-none shadow-inner"
                 style="{{ $hasCustomImage ? 'display: none;' : 'display: flex;' }}">
                {{ $initials }}
            </div>

            <span class="absolute bottom-1 right-1 bg-primary-500 text-white rounded-full p-1 flex items-center justify-center shadow-sm" title="Verified Pharmacy" style="width: 24px; height: 24px;">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
            </span>
        </div>
    </div>
    <div class="p-6 text-center flex-grow flex flex-col">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $pharmacy->name }}</h3>
        
        <div class="flex items-center justify-center my-3">
            @php
                $rating = round($pharmacy->avgratting);
            @endphp
            @for ($i = 1; $i <= 5; $i++)
                @if ($i <= $rating)
                    <i class="fas fa-star text-yellow-400"></i>
                @else
                    <i class="far fa-star text-gray-400"></i>
                @endif
            @endfor
            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $pharmacy->totalreview ?? 0 }})</span>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 h-10 overflow-hidden flex-grow">
            <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>{{ Str::limit($pharmacy->address, 50) }}
        </p>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-auto">
            <a href="{{ url('viewpharmacy', $pharmacy->slug) }}" class="w-full inline-block bg-primary-500 text-white font-semibold py-2 px-4 rounded-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 transition-colors">
                View Details
            </a>
        </div>
    </div>
</div>
