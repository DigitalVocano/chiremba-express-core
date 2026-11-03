@extends('layouts.patient')
@section('title')
    {{__("Referral Dashboard")}}
@stop
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200 mb-6">{{__("Referral Dashboard")}}</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Your Code & Points -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Your Referral Code</h3>
                <div class="my-4 p-3 bg-primary-100 dark:bg-primary-800/50 border-2 border-dashed border-primary-400 dark:border-primary-600 rounded-lg">
                    <p class="text-3xl font-bold text-primary-600 dark:text-primary-300 tracking-widest">{{ $userdata->referral_code }}</p>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">Share this code with friends. When they sign up, you earn points!</p>
                <!-- Share Buttons -->
                <div class="mt-4 flex items-center justify-center space-x-2" 
                     x-data="referralShare(
                        '{{ urlencode('Join KumbaCare for excellent healthcare services! Use my referral code to get started: ' . $userdata->referral_code) }}',
                        '{{ urlencode(route('patientregister', ['code' => $userdata->referral_code])) }}',
                        '{{ $userdata->referral_code }}'
                     )">
                    <a :href="`https://wa.me/?text=${shareText}`" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors" title="Share on WhatsApp">
                        <i class="fab fa-whatsapp text-xl"></i>
                    </a>
                    <a :href="`https://www.facebook.com/sharer/sharer.php?u=${shareUrl}&quote=${shareText}`" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors" title="Share on Facebook">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                    <button @click="copyToClipboard()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-500 text-white hover:bg-gray-600 transition-colors" :title="copied ? 'Copied!' : 'Copy Code'">
                        <i class="fas fa-copy text-lg" x-show="!copied"></i>
                        <i class="fas fa-check text-lg" x-show="copied" style="display: none;"></i>
                    </button>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Your Points Balance</h3>
                <p class="text-5xl font-bold text-gray-800 dark:text-gray-100 mt-2">{{ $userdata->referral_points }}</p>
            </div>
        </div>

        <!-- Right Column: Rewards & History -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Redeem Rewards Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Redeem Your Points</h3>
                <div class="space-y-4">
                    @forelse($rewards as $reward)
                        <div class="flex items-center justify-between p-4 border dark:border-gray-700 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $reward->name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reward->description }}</p>
                                <p class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ $reward->points_required }} Points</p>
                            </div>
                            <form action="{{ route('user.referrals.redeem') }}" method="POST">
                                @csrf
                                <input type="hidden" name="reward_id" value="{{ $reward->id }}">
                                <button type="submit" class="px-4 py-2 bg-primary-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-600 disabled:bg-gray-400"
                                    {{ $userdata->referral_points < $reward->points_required ? 'disabled' : '' }}>
                                    Redeem
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400">No rewards available at the moment.</p>
                    @endforelse
                </div>
            </div>

            <!-- Referral History Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Your Referral History</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Referred User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Points Earned</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($referrals as $referral)
                                @if($referral->referred)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('upload/profile/' . ($referral->referred->profile_pic ?: 'profile.png')) }}" alt="{{ $referral->referred->name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                        {{ $referral->referred->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $referral->created_at->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400 text-right">+{{ $referral->points_awarded }}</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">You haven't referred anyone yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 @if($referrals->hasPages())
                    <div class="mt-4">
                        {{ $referrals->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('footer_scripts')
<script>
    function referralShare(shareText, shareUrl, referralCode) {
        return {
            copied: false,
            shareText: shareText,
            shareUrl: shareUrl,
            referralCode: referralCode,
            copyToClipboard() {
                navigator.clipboard.writeText(this.referralCode).then(() => {
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2500);
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            }
        }
    }
</script>
@endsection
