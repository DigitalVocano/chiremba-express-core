<div>
    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Reviews for {{$data->name}}</h3>

    <!-- Rating Summary -->
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-8">
        <div class="flex flex-col sm:flex-row items-center sm:items-start sm:space-x-8">
            <div class="text-center mb-4 sm:mb-0">
                <h4 class="text-5xl font-bold text-gray-800 dark:text-white">{{ number_format($data->avgratting ?? 0, 1) }}</h4>
                <div class="flex items-center justify-center mt-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= round($data->avgratting ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                    @endfor
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Based on {{$data->totalreview ?? 0}} reviews</p>
            </div>
            <div class="w-full flex-1">
                @php
                    $startrattinglines = $data->startrattinglines ?? ['start5'=>0, 'start4'=>0, 'start3'=>0, 'start2'=>0, 'start1'=>0];
                @endphp
                @for ($i = 5; $i >= 1; $i--)
                <div class="flex items-center space-x-2 mb-1">
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-12">{{ $i }} star</span>
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                        <div class="bg-yellow-400 h-2.5 rounded-full" style="width: {{ $startrattinglines['start'.$i] ?? 0 }}%"></div>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-10 text-right">{{ round($startrattinglines['start'.$i] ?? 0) }}%</span>
                </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="space-y-6 mb-8">
        @forelse($data->reviewslist as $review)
        <div class="flex space-x-4 border-b border-gray-200 dark:border-gray-700 pb-6">
            <img class="h-12 w-12 rounded-full object-cover" src="{{ asset('upload/profile/profile.png') }}" alt="{{ $review->patientls->name ?? 'User' }}">
            <div class="flex-1">
                <div class="flex justify-between items-center">
                    <h5 class="font-semibold text-gray-800 dark:text-white">{{ $review->patientls->name ?? 'Anonymous' }}</h5>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($review->created_at)->format('F d, Y') }}</span>
                </div>
                <div class="flex items-center my-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                    @endfor
                </div>
                <p class="text-gray-600 dark:text-gray-300">{{ $review->description }}</p>
            </div>
        </div>
        @empty
        <p class="text-gray-500 dark:text-gray-400">No reviews yet. Be the first to leave a review!</p>
        @endforelse
    </div>

    <!-- Add Review Form -->
    <div>
        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Leave a Review</h4>
        @if(Session::has("user_id"))
        <form action="{{url('savereview')}}" method="post">
            @csrf
            <input type="hidden" name="doctor_id" value="{{$data->id}}">
            <input type="hidden" name="user_id" value="{{Session::get('user_id')}}">
            
            <div class="mb-4" x-data="{ rating: 0 }">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Rating</label>
                <div class="flex items-center space-x-1">
                    <template x-for="i in 5">
                        <button type="button" @click="rating = i" class="text-2xl focus:outline-none" :class="i <= rating ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-300'">
                            <i class="fas fa-star"></i>
                        </button>
                    </template>
                    <input type="hidden" name="rating3" x-model="rating" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="review_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Your Review</label>
                <textarea name="description" id="review_description" rows="4" required placeholder="Share your experience..." class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200"></textarea>
            </div>

            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Submit Review
            </button>
        </form>
        @else
        <div class="bg-gray-100 dark:bg-gray-700/50 p-4 rounded-lg text-center">
            <p class="text-gray-600 dark:text-gray-300">You must be logged in to leave a review.</p>
            <a href="{{url('patientlogin')}}" class="mt-2 inline-block text-primary-600 dark:text-primary-400 font-semibold hover:underline">Login Now</a>
        </div>
        @endif
    </div>
</div>

