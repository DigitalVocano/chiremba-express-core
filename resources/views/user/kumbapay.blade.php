@extends('user.layout')
@section('title')
KumbaPay
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:title" content="KumbaPay: Flexible Healthcare Financing | {{ config('app.name') }}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="With KumbaPay, you can split the cost of non-insured healthcare services, such as specialist visits, into manageable installments. Care Now, Pay Later."/>
<meta property="og:keyword" content="KumbaPay, healthcare financing, medical loan, pay later, installments, Chiremba Express"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')

<!-- Page Header Start -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-2.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-3 text-white mb-3 animated slideInDown">{{__('KumbaPay')}}</h1>
    
    </div>
</div>
<!-- Page Header End -->

<!-- KumbaPay Details Start -->
<div class="container-xxl py-16" x-data="{ prescriptionModal: false }">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <p class="text-primary-500 font-semibold uppercase tracking-wider">KumbaPay</p>
                <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2 mb-4">Flexible Healthcare Financing: Care Now, Pay Later</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-4">At Chiremba Express, we believe that everyone deserves access to quality healthcare, regardless of their financial situation. That's why we're excited to introduce KumbaPay, an innovative financing solution that allows you to pay for healthcare services in installments.</p>
                <p class="text-gray-600 dark:text-gray-400 mb-6">KumbaPay is exclusively available to our premium plan subscribers. With KumbaPay, you can split the cost of non-insured healthcare services, such as specialist visits, into manageable installments.</p>
                @if(Session::has("user_id") && Session::get("role_id") == '1')
                    <button @click="prescriptionModal = true" class="inline-block bg-primary-500 text-white font-semibold py-3 px-6 rounded-md hover:bg-primary-600 transition-colors">Apply for Advance</button>
                @else
                    <button onclick="pleaselogin()" class="inline-block bg-primary-500 text-white font-semibold py-3 px-6 rounded-md hover:bg-primary-600 transition-colors">Apply for Advance</button>
                @endif
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.5s">
                <div class="relative h-96">
                    <img class="absolute top-0 right-0 w-3/4 h-full rounded-lg shadow-lg object-cover" src="{{asset('kumbacare/img/insurance-1.webp')}}" alt="Healthcare financing">
                    <img class="absolute bottom-0 left-0 w-1/2 rounded-lg shadow-lg border-8 border-white dark:border-gray-900 object-cover" src="{{asset('kumbacare/img/kumbapay-2.jpg')}}" alt="Easy payment">
                </div>
            </div>
        </div>
    </div>
    <!-- Upload Prescription Modal -->
    <div x-show="prescriptionModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="prescriptionModalLabel" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="prescriptionModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-init="setTimeout(() => showMessage = false, 5000)" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="prescriptionModal = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="prescriptionModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{url('upload_kumbapay')}}" method="post" enctype="multipart/form-data">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-file-invoice-dollar text-primary-600 dark:text-primary-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="prescriptionModalLabel">Apply for Advance</h3>
                                <div class="mt-4 space-y-4">
                                    {{csrf_field()}}
                                    <div>
                                        <label for="identity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload ID</label>
                                        <input type="file" name="identity" id="identity" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600">
                                    </div>
                                    <div>
                                        <label for="prescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Prescription</label>
                                        <input type="file" name="prescription" id="prescription" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600">
                                    </div>
                                    <div>
                                        <label for="fullname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                                        <input type="text" name="fullname" id="fullname" placeholder="Enter Full Name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                    </div>
                                    <div>
                                        <label for="phone_no1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone No</label>
                                        <input type="text" name="phone_no" id="phone_no1" placeholder="Enter Your Phone number" value="{{Session::get('user_phone_no')}}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                    </div>
                                    <div>
                                        <label for="address1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                        <input type="text" name="address" id="address1" placeholder="Enter Address" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                    </div>
                                    <div>
                                        <label for="professionaljob" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Professional Job</label>
                                        <input type="text" name="professionaljob" id="professionaljob" placeholder="Enter Professional Job" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                    </div>
                                    <div>
                                        <label for="loanamount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Loan Amount</label>
                                        <input type="text" name="loanamount" id="loanamount" placeholder="Enter loan Amount" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm text-white">
                            Submit
                        </button>
                        <button @click="prescriptionModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- KumbaPay Details End -->

<!-- Benefits Start -->
<div class="container-fluid bg-gray-50 dark:bg-gray-800 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">Benefits</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2">Benefits of KumbaPay</h1>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <div class="bg-white dark:bg-gray-700 text-center h-full p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                        <i class="fa fa-hand-holding-usd fa-2x"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">Flexible Payments</h4>
                    <p class="text-gray-600 dark:text-gray-400">Pay for healthcare services in installments, rather than all at once.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.3s">
                <div class="bg-white dark:bg-gray-700 text-center h-full p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                        <i class="fa fa-wallet fa-2x"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">Reduced Financial Stress</h4>
                    <p class="text-gray-600 dark:text-gray-400">Spread the cost of healthcare services over time, reducing the burden on your finances.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.5s">
                <div class="bg-white dark:bg-gray-700 text-center h-full p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                        <i class="fa fa-user-md fa-2x"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">Access to Specialists</h4>
                    <p class="text-gray-600 dark:text-gray-400">Get the specialist care you need without worrying about upfront costs.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Benefits End -->

<!-- FAQ Start -->
<div class="container-xxl py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">FAQ</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2">Frequently Asked Questions</h1>
        </div>
        <div class="max-w-3xl mx-auto wow fadeInUp" data-wow-delay="0.3s" x-data="{ active: 1 }">
            <div class="space-y-4">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 1 ? null : 1)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">What is KumbaPay?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 1 }"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div x-show="active === 1" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>KumbaPay is a flexible healthcare financing solution offered exclusively to Chiremba Express premium plan subscribers. It allows you to pay for non-insured healthcare services in installments, making quality healthcare more accessible.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 2 ? null : 2)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">How much does the Chiremba Express premium plan cost?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 2 }"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div x-show="active === 2" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>The Chiremba Express premium plan costs $15 per year. This annual fee unlocks access to KumbaPay and other exclusive benefits.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 3 ? null : 3)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">What kind of healthcare services can I finance with KumbaPay?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 3 }"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div x-show="active === 3" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>KumbaPay can be used to finance a variety of non-insured healthcare services, including specialist visits, diagnostic tests, surgical procedures, and other out-of-pocket medical expenses.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 4 ? null : 4)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">How do I apply for KumbaPay?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 4 }"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div x-show="active === 4" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>To apply for KumbaPay, you need to be a Chiremba Express premium plan subscriber. Once you've upgraded your plan, you can contact our customer support team to discuss your financing options and create a payment plan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FAQ End -->
@stop
@section('footer')
@stop
