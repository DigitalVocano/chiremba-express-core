@extends('user.layout')

@section('title')
Chiremba Express for Corporates
@stop

@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:title" content="Chiremba Express for Corporates: Employee Wellness & Productivity"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Enhance employee wellness and productivity with Chiremba Express's comprehensive telemedicine and corporate wellness solutions."/>
<meta property="og:keyword" content="corporate wellness, employee health, telemedicine, productivity, Chiremba Express, corporate healthcare"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop

@section('content')

<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-1.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-3 text-white mb-3 animated slideInDown">Chiremba Express for Corporates</h1>
    </div>
</div>
<section class="py-16 bg-gray-50 dark:bg-gray-900" x-data="{ showQuoteModal: false }">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">Employee Wellness & Productivity</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2">Enhancing Your Team's Health & Performance</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-4">In today’s fast-paced corporate world, employee health and well-being directly impact productivity, engagement, and overall business success. Chiremba Express for Corporates offers a comprehensive telemedicine and wellness solution designed to keep your workforce healthy, happy, and highly productive—all through a seamless virtual platform.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-16">
            <div class="wow fadeInUp" data-wow-delay="0.3s">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Why Chiremba Express for Your Business?</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-4">We provide a fully digital healthcare experience that eliminates the need for in-person visits, long waiting times, and pharmacy runs. Our corporate wellness program includes:</p>
                <ul class="text-gray-700 dark:text-gray-300 space-y-2"> {{-- Removed list-disc and list-inside here --}}
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Free GP Doctor Consultations</strong> – 24/7 access to licensed doctors via telemedicine.</li>
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Free Medication Delivery</strong> – Prescriptions delivered directly to your corporate address.</li>
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Free Blood Pressure Machines</strong> – Track vital signs for early detection of health risks.</li>
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Free Wellness Dashboard</strong> – Monitor employee health trends and vital observations.</li>
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i><strong>Wellness Sessions</strong> – Virtual workshops on stress management, nutrition, and fitness.</li>
                </ul>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.5s">
                <img src="{{ asset('kumbacare/img/corporate-wellness.jpg') }}" alt="Corporate Wellness" class="rounded-lg shadow-lg w-full h-auto object-cover">
            </div>
        </div>

        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">How Chiremba Express Boosts Corporate Productivity</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 h-full flex flex-col items-center text-center">
                    <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-chart-line fa-3x"></i></div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Reduces Sick Leaves & Absenteeism</h3>
                    <p class="text-gray-600 dark:text-gray-400">With instant access to doctors and medication, employees can address health concerns early, preventing prolonged illnesses and unnecessary absences.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.3s">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 h-full flex flex-col items-center text-center">
                    <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-clock fa-3x"></i></div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Saves Time with Virtual Healthcare</h3>
                    <p class="text-gray-600 dark:text-gray-400">No more long clinic queues or wasted hours—employees consult doctors in minutes, right from their workspace or home.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.5s">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 h-full flex flex-col items-center text-center">
                    <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-shield-alt fa-3x"></i></div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Promotes Preventive Care</h3>
                    <p class="text-gray-600 dark:text-gray-400">Regular health monitoring (blood pressure, wellness dashboards) helps detect risks early, keeping chronic conditions in check.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.7s">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 h-full flex flex-col items-center text-center">
                    <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-smile fa-3x"></i></div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Enhances Employee Well-being & Morale</h3>
                    <p class="text-gray-600 dark:text-gray-400">Wellness sessions on mental health, ergonomics, and nutrition lead to happier, more engaged employees.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.9s">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 h-full flex flex-col items-center text-center">
                    <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-dollar-sign fa-3x"></i></div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Cost-Effective for Employers</h3>
                    <p class="text-gray-600 dark:text-gray-400">Reduce healthcare costs by minimizing emergency visits and hospitalizations through proactive care.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="1.1s">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 h-full flex flex-col items-center text-center">
                    <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-laptop-medical fa-3x"></i></div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Everything Delivered Virtually – Zero Hassle!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Chiremba Express integrates seamlessly into your corporate structure without disrupting workflow. All consultations, prescriptions, and wellness programs are managed digitally, ensuring convenience and efficiency.</p>
                </div>
            </div>
        </div>

        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">Invest in Your Team’s Health, Reap the Rewards</h2>
            <p class="text-gray-600 dark:text-gray-400">Healthy employees are productive employees. With Chiremba Express, you’re not just offering healthcare—you’re fostering a culture of wellness that drives performance and loyalty.</p>
        </div>

        <div class="mb-16">
            <h2 class="text-center text-3xl font-bold text-gray-800 dark:text-white mb-8 wow fadeInUp" data-wow-delay="0.1s">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ url('viewspecialist') }}" class="block p-6 bg-primary-500 text-white rounded-lg shadow-lg hover:bg-primary-600 transition-colors duration-200 text-center flex flex-col items-center justify-center wow fadeInUp" data-wow-delay="0.2s">
                    <i class="fas fa-user-md fa-3x mb-3"></i>
                    <span class="text-xl font-semibold">Find a Doctor</span>
                </a>
                <button type="button" @click="showQuoteModal = true" class="block p-6 bg-primary-500 text-white border-2 border-primary-500 rounded-lg shadow-lg hover:bg-primary-500 hover:text-white transition-colors duration-200 text-center flex flex-col items-center justify-center wow fadeInUp" data-wow-delay="0.4s">
                    <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                    <span class="text-xl font-semibold">Get a Quote</span>
                </button>
                <a href="{{ url('searchpharmacy') }}" class="block p-6 bg-secondary-500 text-white rounded-lg shadow-lg hover:bg-secondary-600 transition-colors duration-200 text-center flex flex-col items-center justify-center wow fadeInUp" data-wow-delay="0.6s">
                    <i class="fas fa-pills fa-3x mb-3"></i>
                    <span class="text-xl font-semibold">Buy Medication</span>
                </a>
            </div>
        </div >

        <div x-show="showQuoteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            style="display: none;"
        >
            <div @click.away="showQuoteModal = false" class="relative w-full max-w-md max-h-full bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button" @click="showQuoteModal = false" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="px-6 py-6 lg:px-8">
                    <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Request a Corporate Quote</h3>
                    <form class="space-y-6" action="{{ url('send-corporate-quote') }}" method="POST">
                        @csrf
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your Name</label>
                            <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="John Doe" required>
                        </div>
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your Email</label>
                            <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="name@company.com" required>
                        </div>
                        <div>
                            <label for="company_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Company Name</label>
                            <input type="text" name="company_name" id="company_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="ABC Corp" required>
                        </div>
                        <div>
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone Number</label>
                            <input type="text" name="phone" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="+263 771 234 567" required>
                        </div>
                        <div>
                            <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Message (Optional)</label>
                            <textarea name="message" id="message" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="Tell us about your organization's needs..."></textarea>
                        </div>
                        <button type="submit" class="w-full text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Send Request</button>
                    </form>
                </div>
            </div>
        </div>

</div >
</section>

@stop

@section('footer_scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stop

@section('footer')
@stop
