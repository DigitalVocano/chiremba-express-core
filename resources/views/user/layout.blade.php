<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', mobileMenuOpen: false, cartSidebarOpen: false, desktopCartOpen: {{ Session::has('show_cart_dropdown') ? 'true' : 'false' }} }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
      <title>@yield('title', 'Chiremba Express: Your One-Stop Platform for Healthcare in Zimbabwe')</title>
      @yield('meta-data')
      @php $fav = app\models\Setting::find(1)->favicon; @endphp
      <link rel="icon" href="{{asset('image_web/'.$fav)}}">

      <link href="{{ mix('css/app.css') }}" rel="stylesheet">

      {{-- jQuery UI CSS for Datepicker --}}
      <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
      <!-- Alpine.js Core -->
      <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
      <!-- Alpine.js Collapse Plugin -->
      <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

      <!-- Google Tag Manager -->
      @if(isset($setting) && $setting->google_tag_manager_id)
      <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
      'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','{{ $setting->google_tag_manager_id }}');</script>
      @endif
      <!-- End Google Tag Manager -->

      <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
      </script>

       <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">

      <style type="text/css">
      @media only screen and (min-width: 500px) {
#mobileshow  {
display: none !important;}
}

      @media only screen and (max-width: 500px) {
#mobilehide  {
display: none !important;}
}
</style>
      <style>
           body { font-family: 'Inter', sans-serif; }
           h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, .display-1, .display-2, .display-3, .display-4, .display-5, .display-6, .navbar-brand, .nav-link, .btn {
               font-family: 'Poppins', sans-serif;
           }
      </style>
      <style>
        /* Ensure datepicker is above other elements */
        .ui-datepicker { z-index: 9999 !important; }
      </style>


      @if($setting->is_rtl=='1')
           <link href="{{asset('front_pro/assets/css/rtl.css?v=232')}}" rel="stylesheet">
           <style>
               .fr{
                 float: right; }
               .fl{ float: left; }
           </style>
      @else
      @endif
       <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.0/sweetalert.min.css">

      {{-- Google Maps API - Added async and removed deprecated sensor parameter --}}
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <style>
           .light{
                 color:gray;
           }
      </style>
      <style>
        /* Preloader styles */
        .preloader {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 999999;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease-out;
        }
        .preloader.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .heart-preloader {
            position: relative;
            width: 60px;
            height: 60px;
            background-color: #dc3545; /* Red */
            transform: rotate(-45deg);
            animation: heartbeat 1.4s linear infinite;
        }
        .heart-preloader:after,
        .heart-preloader:before {
            content: "";
            position: absolute;
            width: 60px;
            height: 60px;
            background-color: #dc3545; /* Red */
            border-radius: 50%;
        }
        .heart-preloader:before { top: -30px; left: 0; }
        .heart-preloader:after { left: 30px; top: 0; }

        @keyframes heartbeat {
            0% { transform: scale(1) rotate(-45deg); }
            50% { transform: scale(0.8) rotate(-45deg); }
            100% { transform: scale(1) rotate(-45deg); }
        }

        /* Styles for app download buttons */
        .app-download-btn {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem; /* py-2 px-4 */
            background-color: #000; /* bg-black */
            color: #fff; /* text-white */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* shadow-md */
            transition: box-shadow 0.2s ease-in-out; /* hover:shadow-lg transition-shadow duration-200 */
        }
        .app-download-btn:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* hover:shadow-lg */
        }
        .app-download-btn img {
            height: 2rem; /* h-8 */
            margin-right: 0.5rem; /* mr-2 */
        }
        .app-download-btn .text-container {
            display: flex;
            flex-direction: column;
            text-align: left;
        }
        .app-download-btn .text-container .text-xs {
            font-size: 0.75rem;
            opacity: 0.8;
        }
        .app-download-btn .text-container .text-base {
            font-size: 1rem;
            font-weight: 600; /* font-semibold */
        }
      </style>

   </head>
   <body class="bg-white dark:bg-gray-900">
      @php
          $cartData = [
              'items' => collect(),
              'total' => 0,
              'count' => 0,
              'currency' => explode("-", $setting->currency),
          ];
          if (Session::has("user_id") && Session::get("role_id") == '1') {
              $userId = Session::get('user_id');
              $cartItems = \Darryldecode\Cart\Facades\CartFacade::session($userId)->getContent();
              $cartData['items'] = $cartItems;
              $cartData['total'] = \Darryldecode\Cart\Facades\CartFacade::session($userId)->getTotal();
              $cartData['count'] = $cartItems->count();
          }
      @endphp
      @if(isset($setting) && $setting->google_tag_manager_id)
      <!-- Google Tag Manager (noscript) -->
      <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $setting->google_tag_manager_id }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
      <!-- End Google Tag Manager (noscript) -->
      @endif
      <div class="preloader">
          <div class="heart-preloader"></div>
      </div>
      @include('cookieConsent::index') {{-- Keep cookie consent --}}
      @if($setting->is_rtl=='1')
      <div class="boxed_wrapper rtl"> {{-- Keep RTL wrapper --}}
      @else
      <div class="boxed_wrapper bg-white dark:bg-gray-900"> {{-- Keep main wrapper --}}
      @endif

      <header class="header-section">
          <nav class="bg-primary-500 shadow-md fixed top-0 w-full z-50 py-3">
              <div class="container mx-auto flex items-center justify-between px-4">
                  <a class="flex-shrink-0" href="{{url('/')}}">
                      <img src="{{asset('image_web/').'/'.$setting->logo}}" alt="Chiremba Express Logo" class="w-44">
                  </a>
                  <button class="lg:hidden text-white focus:outline-none" type="button" @click="mobileMenuOpen = !mobileMenuOpen">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                      </svg>
                  </button>
                  <div class="hidden lg:flex flex-grow items-center justify-end">
                      <ul class="flex space-x-6">
                          <li><a class="text-white hover:text-gray-200 font-medium" href="{{url('/')}}">Home</a></li>
                          <li class="relative" x-data="{ open: false }">
                              <a class="text-white hover:text-gray-200 font-medium cursor-pointer flex items-center" @click="open = !open">
                                  Search <i class="fas fa-chevron-down ml-1 text-xs"></i>
                              </a>
                              <ul x-show="open" @click.away="open = false" class="absolute bg-white dark:bg-gray-700 shadow-lg rounded-md py-2 w-40 mt-2 z-10" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                  <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{url('searchdoctor')}}">Doctors</a></li>
                                  <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{url('viewspecialist')}}">Specialist</a></li>
                                  <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{url('searchpharmacy')}}">Pharmacy</a></li>
                              </ul>
                          </li>
                          <li><a class="text-white hover:text-gray-200 font-medium" href="{{url('medical-aid')}}">Medical Aid</a></li>
                          <li><a class="bg-pink-600 hover:bg-pink-500 text-white px-3 py-1 rounded-full font-semibold text-sm flex items-center gap-1.5 shadow-sm" href="{{route('femtech.hub')}}"><i class="fas fa-baby-carriage text-xs"></i> Maternal & SRHR</a></li>
                          <li><a class="text-white hover:text-gray-200 font-medium text-sm flex items-center gap-1" href="{{route('chw.portal')}}"><i class="fas fa-user-nurse text-xs"></i> CHW Mode</a></li>
                          <li class="relative" x-data="{ open: false }">
                              <a class="text-white hover:text-gray-200 font-medium cursor-pointer flex items-center" @click="open = !open">
                                  About <i class="fas fa-chevron-down ml-1 text-xs"></i>
                              </a>
                              <ul x-show="open" @click.away="open = false" class="absolute bg-white dark:bg-gray-700 shadow-lg rounded-md py-2 w-40 mt-2 z-10" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                  <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{url('aboutus')}}">About Us</a></li>
                                  <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{url('contactus')}}">Contact Us</a></li>
                                  <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{url('Privacy_Policy')}}">Privacy</a></li>
                              </ul>
                          </li>
                          <li><a class="text-white hover:text-gray-200 font-medium" href="/magazine" target="_blank">Magazine</a></li>
                          <li><a class="text-white hover:text-gray-200 font-medium flex items-center gap-1.5" href="{{url('/developers/api')}}"><i class="fas fa-code text-xs"></i> Developers</a></li>
                      </ul>
                      <div class="ml-6 flex items-center space-x-2">
                          <button @click="darkMode = !darkMode" type="button" class="text-white hover:bg-primary-600 focus:outline-none focus:ring-4 focus:ring-primary-600 rounded-lg text-sm p-2.5">
                              <svg x-show="!darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
                              <svg x-show="darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                          </button>
                          @php
                              $isAdminSession = Session::has('admin') || (class_exists('\Cartalyst\Sentinel\Laravel\Facades\Sentinel') && \Cartalyst\Sentinel\Laravel\Facades\Sentinel::check());
                              $isUserSession = Session::has("user_id") && in_array(Session::get('role_id'), ['1', '2', '3']);
                              $isLoggedInAny = $isAdminSession || $isUserSession;

                              $walletBalance = null;
                              $dashboardUrl = '#';
                              $walletUrl = '#';
                              $logoutUrl = '#';
                              $dashboardText = 'My Dashboard';

                              if ($isAdminSession) {
                                  $dashboardUrl = url('admin/dashboard');
                                  $logoutUrl = url('admin/logout');
                                  $dashboardText = 'Admin Dashboard';
                              } elseif ($isUserSession) {
                                  $userId = Session::get('user_id');
                                  $roleId = Session::get('role_id');
                                  $currency = explode("-", \App\Models\Setting::find(1)->currency ?? 'USD-USD');

                                  if ($roleId == 1) { // Patient
                                      $walletOwner = \App\Models\Patient::with('wallet')->find($userId);
                                      $dashboardUrl = route('userdashboard');
                                      $walletUrl = route('medical.wallet');
                                      $logoutUrl = route('user.logout');
                                  } elseif ($roleId == 2) { // Doctor
                                      $walletOwner = \App\Models\Doctors::with('wallet')->find($userId);
                                      $dashboardUrl = route('doctordashboard');
                                      $walletUrl = route('doctor.wallet');
                                      $logoutUrl = route('doctor.logout');
                                  } elseif ($roleId == 3) { // Pharmacy
                                      $walletOwner = \App\Models\Doctors::with('wallet')->find($userId);
                                      $dashboardUrl = route('pharmacydashboard');
                                      $walletUrl = route('pharmacy.wallet');
                                      $logoutUrl = route('pharmacy.logout');
                                  }

                                  if (isset($walletOwner) && $walletOwner && $walletOwner->wallet) {
                                      $walletBalance = $walletOwner->wallet->balance;
                                  }
                              }
                          @endphp

                          @if($isLoggedInAny)
                              <div class="relative" x-data="{ open: false }">
                                  <button class="bg-white text-primary-600 px-4 py-2 rounded-md hover:bg-gray-100 focus:outline-none flex items-center font-medium shadow-sm" @click="open = !open">
                                      <i class="fa fa-user-circle mr-2"></i> Dashboard <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                  </button>
                                  <ul x-show="open" @click.away="open = false" class="absolute bg-white dark:bg-gray-700 shadow-lg rounded-md py-2 w-44 right-0 mt-2 z-10" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                      <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 font-medium" href="{{ $dashboardUrl }}"><i class="fas fa-columns mr-2 text-primary-500"></i> {{ $dashboardText }}</a></li>
                                      <li><hr class="border-gray-200 dark:border-gray-600 my-1"></li>
                                      <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{ $logoutUrl }}"><i class="fas fa-sign-out-alt mr-2 text-red-500"></i> Logout</a></li>
                                  </ul>
                              </div>
                              @if($walletBalance !== null)
                                  <a href="{{ $walletUrl }}" class="ml-2 bg-green-100 text-green-800 text-sm font-medium px-3 py-2 rounded-md dark:bg-green-900 dark:text-green-200 flex items-center hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                                      <i class="fas fa-wallet mr-2"></i>
                                      <span>{{ $currency[1] ?? '$' }}{{ number_format($walletBalance, 2) }}</span>
                                  </a>
                              @endif
                          @else
                              <div class="relative" x-data="{ open: false }">
                                  <button class="border border-white text-white px-4 py-2 rounded-md hover:bg-white hover:text-primary-500 focus:outline-none" @click="open = !open">Login</button>
                                  <ul x-show="open" @click.away="open = false" class="absolute bg-white dark:bg-gray-700 shadow-lg rounded-md py-2 w-40 right-0 mt-2 z-10" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                      <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{url('patientlogin')}}">Patient Login</a></li>
                                      <li><a class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="{{url('profilelogin')}}">Provider Login</a></li>
                                  </ul>
                              </div>
                              <a href="{{url('patientregister')}}" class="bg-white text-primary-600 px-4 py-2 rounded-md hover:bg-gray-100 focus:outline-none">Register</a>
                              <a href="{{url('doctorregister')}}" class="bg-white text-primary-600 px-4 py-2 rounded-md hover:bg-gray-100 focus:outline-none">{{__('Join as Provider')}}</a>
                          @endif

                        {{-- Cart Icon and Dropdown --}}
                        @if(Session::has("user_id") && Session::get("role_id") == '1')
                            <div class="relative ml-4 flow-root lg:ml-6">
                                <a href="#" @click.prevent="desktopCartOpen = !desktopCartOpen" class="group -m-2 p-2 flex items-center text-white relative">
                                    <i class="fas fa-shopping-cart flex-shrink-0 h-6 w-6"></i>
                                    @if($cartData['count'] > 0)
                                        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">{{ $cartData['count'] }}</span>
                                    @endif
                                    <span class="sr-only">items in cart, view bag</span>
                                </a>
                                <div x-show="desktopCartOpen" @click.away="desktopCartOpen = false"
                                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 shadow-lg rounded-lg ring-1 ring-black ring-opacity-5 z-20" style="display: none;">
                                    <div class="p-4 relative">
                                        <div class="flex items-start justify-between">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Shopping Cart</h3>
                                            <div class="ml-3 h-7 flex items-center">
                                                <button type="button" @click="desktopCartOpen = false" class="-m-2 p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                                    <span class="sr-only">Close panel</span>
                                                    <i class="fas fa-times h-6 w-6"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <ul role="list" class="my-4 divide-y divide-gray-200 dark:divide-gray-700 max-h-60 overflow-y-auto">
                                            @forelse($cartData['items'] as $item)
                                                <li class="flex py-4">
                                                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
                                                        <img src="{{ asset('upload/pharmacymedicine/' . $item->attributes->image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover object-center">
                                                    </div>
                                                    <div class="ml-4 flex flex-1 flex-col">
                                                        <div>
                                                            <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                                <h3><a href="#">{{ $item->name }}</a></h3>
                                                                <p class="ml-4">{{ $cartData['currency'][1] }}{{ number_format($item->price * $item->quantity, 2) }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-1 items-end justify-between text-sm">
                                                            <p class="text-gray-500 dark:text-gray-400">Qty {{ $item->quantity }}</p>
                                                            <div class="flex">
                                                                <a href="{{ url('removeCartItem/' . $item->id) }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-600">Remove</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <p class="py-6 text-center text-gray-500 dark:text-gray-400">Your cart is empty.</p>
                                            @endforelse
                                        </ul>
                                        @if($cartData['count'] > 0)
                                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-4">
                                                <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                    <p>Subtotal</p>
                                                    <p>{{ $cartData['currency'][1] }}{{ number_format($cartData['total'], 2) }}</p>
                                                </div>
                                                @if($walletBalance !== null)
                                                <div class="flex justify-between text-sm font-medium text-green-700 dark:text-green-300">
                                                    <p><i class="fas fa-wallet mr-1"></i> Wallet Balance</p>
                                                    <p>{{ $currency[1] }}{{ number_format($walletBalance, 2) }}</p>
                                                </div>
                                                @endif
                                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Shipping and taxes calculated at checkout.</p>
                                                <div class="mt-6">
                                                    <a href="{{ route('cart.view') }}" class="flex items-center justify-center rounded-md border border-transparent bg-primary-500 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-primary-600">View Cart & Checkout</a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                          @endif
                      </div>
                  </div>
                  {{-- Mobile Menu (hidden by default, shown with Alpine.js) --}}
                  <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="lg:hidden absolute top-full left-0 w-full bg-primary-600 shadow-lg py-4 z-40" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2">
                      <ul class="flex flex-col space-y-2 px-4">
                          <li><a class="block text-white hover:text-gray-200 font-medium py-2" href="{{url('/')}}">Home</a></li>
                          <li x-data="{ open: false }">
                              <a class="block text-white hover:text-gray-200 font-medium py-2 flex items-center justify-between" @click="open = !open">
                                  Search <i class="fas fa-chevron-down ml-1 text-xs" :class="{ 'rotate-180': open }"></i>
                              </a>
                              <ul x-show="open" class="pl-4 border-l border-primary-400 mt-2 space-y-1">
                                  <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{url('searchdoctor')}}">Doctors</a></li>
                                  <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{url('viewspecialist')}}">Specialist</a></li>
                                  <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{url('searchpharmacy')}}">Pharmacy</a></li>
                              </ul>
                          </li>
                          <li><a class="block text-white hover:text-gray-200 font-medium py-2" href="{{url('medical-aid')}}">Medical Aid</a></li>
                          <li x-data="{ open: false }">
                              <a class="block text-white hover:text-gray-200 font-medium py-2 flex items-center justify-between" @click="open = !open">
                                  About <i class="fas fa-chevron-down ml-1 text-xs" :class="{ 'rotate-180': open }"></i>
                              </a>
                              <ul x-show="open" class="pl-4 border-l border-primary-400 mt-2 space-y-1">
                                  <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{url('aboutus')}}">About Us</a></li>
                                  <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{url('contactus')}}">Contact Us</a></li>
                                  <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{url('Privacy_Policy')}}">Privacy</a></li>
                              </ul>
                          </li>
                          <li><a class="block text-white hover:text-gray-200 font-medium py-2" href="/magazine" target="_blank">Magazine</a></li>
                          <li><a class="block text-white hover:text-gray-200 font-medium py-2 flex items-center gap-1.5" href="{{url('/developers/api')}}"><i class="fas fa-code text-xs"></i> Developers</a></li>
                          @if($isLoggedInAny)
                              <li x-data="{ open: false }">
                                  <a class="block text-white font-medium py-2 flex items-center justify-between" @click="open = !open">
                                      <i class="fa fa-user-circle mr-2"></i> Dashboard <i class="fas fa-chevron-down ml-1 text-xs" :class="{ 'rotate-180': open }"></i>
                                  </a>
                                  <ul x-show="open" class="pl-4 border-l border-primary-400 mt-2 space-y-1">
                                      <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{ $dashboardUrl }}">{{ $dashboardText }}</a></li>
                                      <li><hr class="border-primary-400 my-1"></li>
                                      <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{ $logoutUrl }}">Logout</a></li>
                                  </ul>
                              </li>
                          @else
                              <li x-data="{ open: false }">
                                  <a class="block text-white hover:text-gray-200 font-medium py-2 flex items-center justify-between" @click="open = !open">
                                      Login <i class="fas fa-chevron-down ml-1 text-xs" :class="{ 'rotate-180': open }"></i>
                                  </a>
                                  <ul x-show="open" class="pl-4 border-l border-primary-400 mt-2 space-y-1">
                                      <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{url('patientlogin')}}">Patient Login</a></li>
                                      <li><a class="block px-4 py-2 text-white hover:bg-primary-500" href="{{url('profilelogin')}}">Provider Login</a></li>
                                  </ul>
                              </li>
                              <li><a href="{{url('patientregister')}}" class="block bg-white text-primary-600 px-4 py-2 rounded-md text-center hover:bg-gray-100 focus:outline-none">Register</a></li>
                              <li><a href="{{url('doctorregister')}}" class="block bg-white text-primary-600 px-4 py-2 rounded-md text-center hover:bg-gray-100 focus:outline-none">{{__('Join as Provider')}}</a></li>
                          @endif
                          <li>
                            <button @click="toggleDarkMode()" class="w-full flex items-center justify-between text-white hover:text-gray-200 font-medium py-2">
                                <span>Toggle Theme</span>
                                <span class="p-2 rounded-lg hover:bg-primary-500">
                                    <svg x-show="!darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
                                    <svg x-show="darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                                </span>
                            </button>
                          </li>
                      </ul>
                  </div>
              </div>
          </nav>
      </header>
      <div class="pt-24"> {{-- Adjusted padding-top for fixed header --}}
          {{-- Global Session Message Display --}}
          @if(Session::has('message'))
              <div class="container mx-auto px-4 mt-4">
                  <div x-data="{ showMessage: true }"
                       x-show="showMessage"
                       x-transition:enter="transition ease-out duration-300"
                       x-transition:enter-start="opacity-0 transform -translate-y-4"
                       x-transition:enter-end="opacity-100 transform translate-y-0"
                       x-transition:leave="transition ease-in duration-200"
                       x-transition:leave-start="opacity-100 transform translate-y-0"
                       x-transition:leave-end="opacity-0 transform -translate-y-4"
                       x-init="setTimeout(() => showMessage = false, 5000)"
                       class="rounded-lg p-4 {{ Session::get('alert-class', 'bg-blue-100 text-blue-700') == 'alert-success' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' }}" role="alert">
                      <div class="flex items-center justify-between">
                          <p class="font-medium">{{ Session::get('message') }}</p>
                      </div>
                  </div>
              </div>
          @endif
          @yield('content')
      </div>

      {{-- Mobile Floating Cart Button --}}
      @if(Session::has("user_id") && Session::get("role_id") == '1')
        <div x-show="cartSidebarOpen == false && {{ $cartData['count'] }} > 0" x-cloak class="md:hidden fixed bottom-5 right-5 z-40">
            <button @click="cartSidebarOpen = true" class="relative flex items-center justify-center w-16 h-16 bg-primary-500 text-white rounded-full shadow-lg hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <i class="fas fa-shopping-cart text-2xl"></i>
                <span class="absolute -top-1 -right-1 flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-600 rounded-full">{{ $cartData['count'] }}</span>
            </button>
        </div>
      @endif

      {{-- Mobile Right-Side Cart Sidebar --}}
      <div x-show="cartSidebarOpen" x-cloak class="md:hidden fixed inset-0 z-50 overflow-hidden" aria-labelledby="cart-sidebar-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Backdrop -->
            <div x-show="cartSidebarOpen"
                 x-transition:enter="ease-in-out duration-500"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-500"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cartSidebarOpen = false" aria-hidden="true">
            </div>

            <!-- Cart Panel -->
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="cartSidebarOpen"
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-md">
                    <div class="h-full flex flex-col bg-white dark:bg-gray-800 shadow-xl">
                        <div class="flex-1 py-6 overflow-y-auto px-4 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white" id="cart-sidebar-title">Shopping Cart</h2>
                                <div class="ml-3 h-7 flex items-center">
                                    <button type="button" class="-m-2 p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300" @click="cartSidebarOpen = false">
                                        <span class="sr-only">Close panel</span>
                                        <i class="fas fa-times h-6 w-6"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-8">
                                <ul role="list" class="-my-6 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($cartData['items'] as $item)
                                        <li class="flex py-6">
                                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
                                                <img src="{{ asset('upload/pharmacymedicine/' . $item->attributes->image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover object-center">
                                            </div>
                                            <div class="ml-4 flex flex-1 flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                        <h3><a href="#">{{ $item->name }}</a></h3>
                                                        <p class="ml-4">{{ $cartData['currency'][1] }}{{ number_format($item->price * $item->quantity, 2) }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex flex-1 items-end justify-between text-sm">
                                                    <p class="text-gray-500 dark:text-gray-400">Qty {{ $item->quantity }}</p>
                                                    <div class="flex">
                                                        <a href="{{ url('removeCartItem/' . $item->id) }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">Remove</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <p class="py-6 text-center text-gray-500 dark:text-gray-400">Your cart is empty.</p>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        @if($cartData['count'] > 0)
                            <div class="border-t border-gray-200 dark:border-gray-700 py-6 px-4 sm:px-6">
                                <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                    <p>Subtotal</p>
                                    <p>{{ $cartData['currency'][1] }}{{ number_format($cartData['total'], 2) }}</p>
                                </div>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Shipping and taxes calculated at checkout.</p>
                                <div class="mt-6">
                                    <a href="{{ route('cart.view') }}" class="flex items-center justify-center rounded-md border border-transparent bg-primary-500 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-primary-600">View Cart & Checkout</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
      </div>

<div class="bg-gray-800 dark:bg-gray-900 text-gray-300 py-12 wow fadeIn" data-wow-delay="0.1s">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center md:text-left">
                <a href="{{url('/')}}" class="block">
                   <img src="{{asset('image_web/').'/'.$setting->logo}}" alt="Chiremba Express Logo" class="w-48 mx-auto md:mx-0">
                </a>
                <p class="mt-4 mb-6 text-sm">Your trusted partner in digital healthcare, connecting you to doctors and pharmacies anytime, anywhere.</p>
                <div class="flex justify-center md:justify-start space-x-2">
                    <a class="w-9 h-9 flex items-center justify-center rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-colors duration-200" href="https://x.com/i/flow/login?redirect_after_login=%2Fmedhelpzw%2F" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a class="w-9 h-9 flex items-center justify-center rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-colors duration-200" href="https://www.facebook.com/KumbaCare/" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a class="w-9 h-9 flex items-center justify-center rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-colors duration-200" href="YOUR_YOUTUBE_LINK_HERE" target="_blank"><i class="fab fa-youtube"></i></a>
                    <a class="w-9 h-9 flex items-center justify-center rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-colors duration-200" href="https://zw.linkedin.com/company/medhelpapp" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="text-center md:text-left">
                <h4 class="text-primary-500 dark:text-primary-400 text-lg font-semibold mb-4">Get In Touch</h4>
                <p class="text-sm mb-2"><i class="fa fa-map-marker-alt mr-3"></i>{{$setting->address}}</p>
                <p class="text-sm mb-2"><i class="fa fa-phone-alt mr-3"></i>{{$setting->phone}}</p>
                <p class="text-sm"><i class="fa fa-envelope mr-3"></i>{{$setting->email}}</p>
            </div>
            <div class="text-center md:text-left">
                <h4 class="text-primary-500 dark:text-primary-400 text-lg font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a class="text-gray-300 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors duration-200" href="{{url('aboutus')}}">About Us</a></li>
                    <li><a class="text-gray-300 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors duration-200" href="{{url('contactus')}}">Contact Us</a></li>
                    <li><a class="text-gray-300 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors duration-200" href="{{url('searchdoctor')}}">Find a Doctor</a></li>
                    <li><a class="text-gray-300 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors duration-200" href="{{url('medical-aid')}}">Medical Aid Schemes</a></li>
                    <li><a class="text-teal-400 hover:text-teal-300 font-medium transition-colors duration-200 flex items-center gap-1.5" href="{{url('/developers/api')}}"><i class="fas fa-code text-xs"></i> Developer Portal (XAPI)</a></li>
                    <li><a class="text-gray-300 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors duration-200" href="{{url('terms')}}">Terms & Condition</a></li>
                    <li><a class="text-gray-300 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors duration-200" href="{{url('Privacy_Policy')}}">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="text-center">
                <div class="bg-secondary-500 rounded-lg p-6 h-full flex flex-col justify-center items-center">
                    <h4 class="text-white text-lg font-semibold mb-3">Newsletter</h4>
                    <p class="text-white text-sm mb-4">Stay updated with our latest health tips and news.</p>
                    <div class="relative w-full">
                        <input type="email" class="w-full py-3 pl-4 pr-24 bg-white bg-opacity-20 dark:bg-gray-700 border border-white border-opacity-30 dark:border-gray-600 rounded-md text-white dark:text-gray-200 placeholder-white placeholder-opacity-70 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white dark:focus:ring-gray-500 focus:border-transparent text-sm" id="emailnews_footer" placeholder="Your email">
                        <button type="button" onclick="addnewsletter('emailnews_footer')" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white text-secondary-500 px-4 py-2 rounded-md hover:bg-gray-100 focus:outline-none text-sm">SignUp</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center mt-8">
            <a href="{{$setting->app_url}}" class="app-download-btn mr-4" target="_blank">
                <img src="{{asset('kumbacare/img/App_Store_26_icon.webp')}}" alt="App Store">
                <div class="text-container">
                    <span class="text-xs">Download on the</span>
                    <span class="text-base">App Store</span>
                </div>
            </a>
            <a href="{{$setting->playstore_url}}" class="app-download-btn" target="_blank">
                <img src="{{asset('kumbacare/img/1664285914google-play-logo-png.webp')}}" alt="Google Play">
                <div class="text-container">
                    <span class="text-xs">Get it on</span>
                    <span class="text-base">Google Play</span>
                </div>
            </a>
        </div>
    </div>
    <div class="container mx-auto px-4 mt-8 pt-4 border-t border-gray-700 dark:border-gray-600">
        <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500 dark:text-gray-400">
            <div class="text-center md:text-left mb-2 md:mb-0">© <a href="#" class="text-primary-500 dark:text-primary-400 hover:underline">Chiremba Express</a>, All Right Reserved.</div>
            <div class="text-center md:text-right">Designed By <a href="https://digitalvocano.com" class="text-primary-500 dark:text-primary-400 hover:underline">Digital Vocano</a></div>
        </div>
    </div>
</div>
<!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="fixed bottom-8 right-8 bg-primary-500 text-white p-3 rounded-full shadow-lg hover:bg-primary-600 transition-colors duration-200 z-40 dark:bg-primary-600 dark:hover:bg-primary-700" aria-label="Back to top">
        <i class="bi bi-arrow-up text-xl"></i>
    </a>

      </div> {{-- Close boxed_wrapper --}}

      <!-- Scripts -->
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
      <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
      

      {{-- Hidden inputs for JS --}}
      <input type="hidden" id="siteurl" value="{{url('/')}}">
      <input type="hidden" id="currentuserlat">
      <input type="hidden" id="currentuserlong">
      <input type="hidden" id="doctornotavilable" value='Doctor isnot Avilable'>
      <input type="hidden" id="contactsuccssmsg" value='Thank you for getting in touch!'>
      <input type="hidden" id="successlabel" value='Success'>
      <input type="hidden" id="Errorlabel" value='Error'>
      <input type="hidden" id="emailinvaildlabel" value='You have entered an invalid email address'>
      <input type="hidden" id="siteurl" value='{{url('/')}}'>
      <input type="hidden" id="pwdmatch" value='Password And Confirm Password Must Be Same'>
      <input type="hidden" id="currentpwdwrong" value='Current Password is Wrong'>
      <input type="hidden" id="start1val" value='Please Select Start Time First'>
      <input type="hidden" id="loginmsg" value='To book appointment you must login first, please proceed with login now.'>
      <input type="hidden" id="sge" value='Start Time is greater than end time'>
      <input type="hidden" id="sequale" value='Start Time equals end time'>
      <input type="hidden" id="selduration" value='Please Select Any Duration'>
      <input type="hidden" id="startvaltext" value='Start Time'>
      <input type="hidden" id="endvaltext" value='End Time'>
      <input type="hidden" id="durationval" value='Duration'>
      <input type="hidden" id="delete_record" value='delete_record'/>
      <input type="hidden" id="seldurationval" value='Select Duration'>
      <input type="hidden" id="deletetext" value='delete'>

      @yield('footer_scripts')
      <script>
         // Geolocation script (kept as it seems intended for public use)
         if (navigator.geolocation) {
             navigator.geolocation.getCurrentPosition(showPosition);
         } else {
             // Geolocation is not supported by this browser.
         }
         function showPosition(position) {
             $("#currentuserlat").val(position.coords.latitude);
             $("#currentuserlong").val(position.coords.longitude);
         }

         // Cookie consent script (kept as it's a core feature)
         window.laravelCookieConsent = (function () {
            const COOKIE_VALUE = 1;
            const COOKIE_DOMAIN = 'https://chirembaexpress.co.zw/';

            function consentWithCookies() {
                setCookie('laravel_cookie_consent', COOKIE_VALUE, 7300);
                hideCookieDialog();
            }

            function cookieExists(name) {
                return (document.cookie.split('; ').indexOf(name + '=' + COOKIE_VALUE) !== -1);
            }

            function hideCookieDialog() {
                const dialogs = document.getElementsByClassName('js-cookie-consent');
                for (let i = 0; i < dialogs.length; ++i) {
                    dialogs[i].style.display = 'none';
                }
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value
                    + ';expires=' + date.toUTCString()
                    + ';path=/'
                    + '';
            }

            if (cookieExists('laravel_cookie_consent')) {
                hideCookieDialog();
            }

            const buttons = document.getElementsByClassName('js-cookie-consent-agree');
            for (let i = 0; i < buttons.length; ++i) {
                buttons[i].addEventListener('click', consentWithCookies);
            }

            return {
                consentWithCookies: consentWithCookies,
                hideCookieDialog: hideCookieDialog
            };
        })();

        // Newsletter subscription (kept as it's a core feature)
        function addnewsletter(inputId) {
            var email = $("#" + inputId).val();
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (email != "" && regex.test(email)) {
                $.ajax({
                    url: $("#siteurl").val() + "/addnewsletter",
                    method: "get",
                    data: { email: email },
                    success: function(data) {
                        if (data == "done") {
                            Swal.fire(
                              $("#successlabel").val() || 'Success!',
                              $("#contactsuccssmsg").val() || 'You have been subscribed successfully.',
                              'success'
                            )
                            $("#" + inputId).val("");
                        } else {
                            Swal.fire(
                              $("#Errorlabel").val() || 'Error!',
                              'Something went wrong, please try again.',
                              'error'
                            )
                        }
                    }
                });
            } else {
                Swal.fire(
                  $("#Errorlabel").val() || 'Error!',
                  $("#emailinvaildlabel").val() || 'Please enter a valid email address.',
                  'error'
                )
            }
        }
      </script>
      
    {{-- Global JavaScript for Favorite Doctor/Pharmacy functionality --}}
    <script type="text/javascript">
        function userfavorite(id, element) {
            const isFavorited = $(element).hasClass('activefav');
            const confirmText = isFavorited ? "{{ __('Are you sure you want to remove this from your favorites?') }}" : "{{ __('Are you sure you want to add this to your favorites?') }}";
            const confirmTitle = isFavorited ? "{{ __('Remove Favorite') }}" : "{{ __('Add Favorite') }}";

            Swal.fire({
                title: confirmTitle,
                text: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, do it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('userfavorite') }}/" + id,
                        type: "get",
                        dataType: 'json', // Expect JSON response
                        success: function(response) {
                            if (response.op === '0' && window.location.href.indexOf("favouriteuser") > -1) {
                                // If on favorite list page and item is removed, reload
                                Swal.fire({
                                    icon: 'success',
                                    title: response.msg,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            } else if (response.op === '0' || response.op === '1') {
                                // On detail page, update button state
                                $(element).toggleClass('activefav');
                                $(element).find('i').toggleClass('far fas');
                                const newText = $(element).hasClass('activefav') ? " {{__('Remove from Favorites')}}" : " {{__('Add to Favorites')}}";
                                // Update text node directly
                                $(element).contents().filter(function(){ return this.nodeType == 3; }).first().replaceWith(newText);

                                Swal.fire({
                                    icon: 'success',
                                    title: response.msg,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            } else {
                                // Handle cases where user is not logged in (response.op might be '0' but with a login message)
                                // This should be handled by the backend returning 401, but if it returns 200 with a message:
                                if (response.msg && response.msg.includes('Login')) { // Check for login message
                                    Swal.fire({
                                        title: 'Login Required',
                                        html: response.msg, // Use html to render the link
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#3085d6',
                                        cancelButtonColor: '#d33',
                                        confirmButtonText: 'Login Now'
                                    }).then((loginResult) => {
                                        if (loginResult.isConfirmed) {
                                            window.location.href = "{{ url('patientlogin') }}";
                                        }
                                    });
                                } else {
                                    Swal.fire('Error', response.msg || "{{ __('Error processing favorite.') }}", 'error');
                                }
                            }
                        },
                        error: function(xhr) {
                            // Handle 401 Unauthorized specifically
                            if (xhr.status === 401) {
                                const loginUrl = "{{ url('patientlogin') }}";
                                const loginMsg = xhr.responseJSON.msg || "{{ __('Please') }} <a href='" + loginUrl + "'>{{ __('Login') }}</a> {{ __('Your Account') }}";
                                Swal.fire({
                                    title: 'Login Required',
                                    html: loginMsg,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Login Now'
                                }).then((loginResult) => {
                                    if (loginResult.isConfirmed) {
                                        window.location.href = loginUrl;
                                    }
                                });
                            } else {
                                Swal.fire('Error', "{{ __('An error occurred. Please try again.') }}", 'error');
                                console.error("Error processing favorite:", xhr.responseText);
                            }
                        }
                    });
                }
            });
        }

        function pleaselogin(){
            Swal.fire({
                title: 'Login Required',
                text: 'You must be logged in to perform this action.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Login Now'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ url('patientlogin') }}";
                }
            })
        }
    </script>

      {{-- WhatsApp Widget and Preloader Hiding --}}
      <script>
         // WhatsApp Widget Loader
         !function(w,h,a,t,s,p){
             w.whatsapp || (
             s = h.createElement(a),
             s.src = t,
             p = h.getElementsByTagName(a)[0],
             p.parentNode.insertBefore(s, p)
         )}(window, document, "script", "{{ asset('js/whatsappjs.js') }}");
 
         window.addEventListener('load', function() {
             // Hide Preloader
             const preloader = document.querySelector('.preloader');
             if (preloader) {
                preloader.classList.add('hidden'); // Add hidden class to fade out
                setTimeout(() => {
                    preloader.remove();
                }, 500); // Match this duration with the CSS transition duration
             }
 
             // Initialize WhatsApp Widget
             if (typeof whatsapp !== 'undefined') {
                 new whatsapp({
                     agents:[{ name: "Support", phone: "+263787990418", hours: "From 9am - 6pm", cta: "Chiremba Express Support" }]
                 });
             }
         });
       </script>
   </body>
</html>
