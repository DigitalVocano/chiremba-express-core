
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 flex flex-col transition-all duration-300 hover:shadow-xl h-full">
    <figure class="relative w-full h-48 rounded-md overflow-hidden mb-4 bg-primary-50 dark:bg-primary-900/20">
        @php
            $initials = \App\Helpers\Helper::getInitials($doctor->name ?? 'Doctor');
            $hasCustomImage = !empty($doctor->image) && !in_array($doctor->image, ['default.png', 'user.png', 'profile.png', 'defaultpharmacy.png', 'null']);
            $imgPath = $hasCustomImage ? asset('upload/doctors/' . $doctor->image) : '';
        @endphp
        @if($hasCustomImage)
            <img src="{{ $imgPath }}" 
                 alt="{{ $doctor->name ?? 'Doctor' }}" 
                 class="object-cover w-full h-full"
                 onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
        @endif
        <div class="w-full h-full flex items-center justify-center text-white text-3xl font-extrabold tracking-wider bg-gradient-to-br from-primary-600 via-primary-500 to-emerald-600 select-none shadow-inner"
             style="{{ $hasCustomImage ? 'display: none;' : 'display: flex;' }}">
            {{ $initials }}
        </div>
        <a href="javascript:userfavorite('{{ $doctor->id }}')" id="favdoc{{ $doctor->id }}" class="absolute top-3 right-3 text-red-500 hover:text-red-700 text-xl z-10 bg-white/70 dark:bg-gray-800/70 p-1.5 rounded-full" title="{{ $doctor->is_fav == '1' ? 'Remove from favorites' : 'Add to favorites' }}">
            <i class="fas fa-heart"></i>
        </a>
    </figure>
    <div class="flex-grow">
        <h4 class="text-lg font-semibold text-tele-dark-blue dark:text-gray-200 mb-1">{{ $doctor->name ?? 'N/A' }}</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $doctor->departmentls->name ?? 'N/A' }}</p>

        <div class="flex items-center mb-3">
            <div class="flex text-yellow-400 text-sm mr-2">
                @php $rating = round($doctor->avgratting ?? 0); @endphp
                @for ($k = 1; $k <= 5; $k)
                    @if ($k <= $rating)
                        <i class="fas fa-star"></i>
                    @else
                        <i class="far fa-star text-gray-300 dark:text-gray-500"></i>
                    @endif
                @endfor
            </div>
            <span class="text-xs text-gray-600 dark:text-gray-400">({{ $doctor->totalreview ?? 0 }})</span>
        </div>

        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
            <i class="fas fa-map-marker-alt mr-1 text-gray-500 dark:text-gray-400"></i>{{ Str::limit($doctor->address ?? 'N/A', 38) }}
        </p>
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
            <i class="fas fa-clock mr-1 text-gray-500 dark:text-gray-400"></i>{{ $doctor->working_time ?? 'N/A' }}
        </p>
    </div>

    <div class="mt-auto">
        <a href="{{ route('viewdoctor', ['doctor' => $doctor]) }}" class="block w-full text-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
            Visit Now
        </a>
    </div>
</div>
