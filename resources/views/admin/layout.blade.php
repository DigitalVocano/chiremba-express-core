<!DOCTYPE html>
<html lang="en"
    x-data="{
        darkMode: localStorage.getItem('themeMode') === 'dark' || (!('themeMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
        sidebarOpen: window.innerWidth >= 1024
    }"
    x-init="$watch('darkMode', val => { localStorage.setItem('themeMode', val ? 'dark' : 'light') })"
    :class="{'dark': darkMode}">
    <head>
        <meta charset="utf-8" />
        <title>@yield('title')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta content='KumbaCare' name="description" />
        <meta content='KumbaCare' name="author" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{url('/')}}" />
        <meta property="og:title" content='KumbaCare' />
        <meta property="og:image" content="{{Session::get('favicon')}}" />
        <meta property="og:image:width" content="250px" />
        <meta property="og:image:height" content="250px" />
        <meta property="og:site_name" content='KumbaCare' />
        <meta property="og:description" content='{{("meta_description")}}' />
        <meta property="og:keyword" content='{{("Meta Keyword")}}' />
        <link rel="shortcut icon" href="{{Session::get('favicon')}}" />

        <!-- Tailwind & App CSS -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <!-- Icons (Font Awesome and Unicons - assuming these are not Bootstrap-dependent) -->
        <link href="{{asset('admin_design/layouts/vertical/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />

        <!-- Plugin CSS (Non-Bootstrap) -->
        <link href="{{asset('admin_design/layouts/vertical/assets/libs/dropzone/min/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('admin_design/layouts/vertical/assets/libs/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />

        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

        <!-- Global Admin UI Fixes & DataTables Alignment Styles -->
        <style>
            /* Force DataTables wrappers and tables to span 100% full width */
            table.dataTable,
            div.dataTables_wrapper,
            div.dataTables_scrollHead,
            div.dataTables_scrollBody,
            .dataTables_scrollHeadInner,
            .dataTables_scrollHeadInner table {
                width: 100% !important;
                margin: 0 !important;
            }
            table.dataTable th,
            table.dataTable td {
                box-sizing: border-box !important;
            }
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_processing {
                color: inherit !important;
            }
            .dataTables_wrapper .dataTables_paginate {
                margin-top: 1rem;
                display: inline-flex;
                gap: 0.25rem;
                flex-wrap: wrap;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.4rem 0.8rem !important;
                border-radius: 0.5rem !important;
                border: 1px solid #E5E7EB !important;
                background: #FFFFFF !important;
                color: #374151 !important;
                font-size: 0.85rem !important;
                font-weight: 600 !important;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
                border-color: #374151 !important;
                background: #1F2937 !important;
                color: #D1D5DB !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: #0A2B68 !important;
                color: #FFFFFF !important;
                border-color: #0A2B68 !important;
            }

            /* Admin Hero Header Card Gradient */
            .admin-header-card {
                background: linear-gradient(135deg, #0A2B68 0%, #1E3A8A 50%, #065F46 100%);
                box-shadow: 0 10px 25px -5px rgba(10, 43, 104, 0.2);
            }
        </style>

        <script>
            function initMap() {
                // Callback function for Google Maps loading.
            }
        </script>
        <script src='https://maps.google.com/maps/api/js?key={{Config::get("mapdetail.key")}}&libraries=places&loading=async&callback=initMap' async defer></script>

    </head>
    <body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

        <div id="layout-wrapper" class="relative min-h-screen flex flex-col">
            <header id="page-topbar" class="fixed top-0 right-0 left-0 z-30 bg-white dark:bg-gray-800 shadow-md transition-all duration-300 ease-in-out" :class="{'lg:left-60': sidebarOpen}">
                <div class="flex justify-between items-center px-4 h-16">
                    <div class="flex items-center">
                        <!-- Sidebar Toggle (Hamburger) -->
                        <button @click="sidebarOpen = !sidebarOpen" type="button" class="px-3 text-xl text-gray-600 dark:text-gray-300 hover:text-primary-500">
                            <i class="fa fa-fw fa-bars"></i>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4">
                         <!-- Dark Mode Toggle -->
                         <button @click="darkMode = !darkMode" type="button" class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xl">
                            <i x-show="!darkMode" class="uil-moon"></i>
                            <i x-show="darkMode" style="display: none;" class="uil-sun"></i>
                        </button>

                        <!-- Language Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="flex items-center p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span class="hidden xl:inline-block ml-1 font-medium uppercase">{{Session::get('locale') ?? 'en'}}</span>
                                <i class="uil-angle-down hidden xl:inline-block ml-1"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50" style="display: none;">
                                <div class="py-1">
                                    <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url("local/en")}}">● <span class="align-middle">{{ __('english') }}</span></a>
                                    <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url("local/ar")}}">● <span class="align-middle">{{ __('arabic') }}</span></a>
                                    <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url("local/fr")}}">● <span class="align-middle">{{ __('french') }}</span></a>
                                    <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url("local/es")}}">● <span class="align-middle">{{ __('spanish') }}</span></a>
                                    <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url("local/pt")}}">● <span class="align-middle">{{ __('portuguese') }}</span></a>
                                </div>
                            </div>
                        </div>

                        <!-- Fullscreen Toggle -->
                        <div class="hidden lg:inline-block" x-data="{ isFullscreen: false }">
                            <button @click="isFullscreen = !isFullscreen; document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()" type="button" class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xl">
                                <i x-show="!isFullscreen" class="uil-arrows-shrink-h"></i>
                                <i x-show="isFullscreen" style="display: none;" class="uil-compress-arrows"></i>
                            </button>
                        </div>

                        <!-- Notifications Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xl relative" id="bell-button">
                                <i class="uil-bell"></i>
                                <span class="absolute top-0 right-0 flex h-4 w-4">
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-white text-xs items-center justify-center" id="ordercount">0</span>
                                </span>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-50" aria-labelledby="page-header-notifications-dropdown" id="notificationshow" style="display: none;">
                                <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <h6 class="m-0 text-gray-800 dark:text-gray-200"> {{("Notifications")}} </h6>
                                        <p class="text-red-500" id="notificationmsg"></p>
                                    </div>
                                </div>
                                <div data-simplebar style="max-height: 230px;">
                                    <!-- Notification items go here -->
                                </div>
                                <div class="p-2 border-t border-gray-200 dark:border-gray-700 text-center">
                                    <a href="javascript:void(0);" class="text-primary-500 font-medium">{{("View More")}}..</a>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="flex items-center text-left p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                <img class="h-8 w-8 rounded-full object-cover" src="{{asset('upload/profile/').'/'.(Session::get('admin.profile_pic') ?? 'profile.png')}}" alt="Header Avatar" />
                                <span class="hidden xl:inline-block ml-2 font-medium text-gray-700 dark:text-gray-300">{{Session::get("admin.name")}}</span>
                                <i class="uil-angle-down hidden xl:inline-block ml-1 text-gray-700 dark:text-gray-300"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50" style="display: none;">
                                <div class="py-1">
                                    <a class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url('admin/editprofile')}}"><i class="uil uil-user-circle text-lg mr-2"></i> <span>{{("View Profile")}}</span></a>
                                    <a class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400 font-semibold" href="{{url('admin/2fa-setup')}}"><i class="uil uil-shield-check text-lg mr-2 text-indigo-500"></i> <span>{{("2FA Security Setup")}}</span></a>
                                    <a class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url('admin/changepassword')}}"><i class="mdi mdi-key text-lg mr-2"></i> <span>{{("Change Password")}}</span></a>
                                    <a class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url('admin/setting')}}"><i class="uil uil-sliders-v-alt text-lg mr-2"></i> <span>{{("Setting")}}</span></a>
                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                    <a class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{url('admin/logout')}}"><i class="uil uil-sign-out-alt text-lg mr-2"></i> <span>{{("Sign out")}}</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ========== Left Sidebar Start ========== -->
            <div class="vertical-menu fixed top-0 left-0 h-full bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 w-60 z-40 transform transition-transform duration-300 ease-in-out border-r border-gray-200 dark:border-gray-700" :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}" id="sidebar">
                <div class="h-16 flex items-center justify-center border-b border-gray-200 dark:border-gray-700">
                     <a href="{{url('admin/dashboard')}}" class="logo">
                        <img src="{{Session::get('logo')}}" alt="Logo" class="h-8 dark:invert" />
                    </a>
                </div>

                <div class="h-[calc(100%-4rem)] overflow-y-auto p-4">
                     <div id="sidebar-menu">
                        <ul class="space-y-1">
                            <!-- Main -->
                            <li>
                                <a href="{{url('admin/dashboard')}}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/dashboard') ? 'bg-primary-50 text-primary-600 dark:bg-gray-700' : 'text-gray-600 dark:text-gray-300' }}">
                                    <i class="uil-home-alt text-primary-500 mr-3 text-lg"></i>
                                    <span>{{("Dashboard")}}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{route('admin.system_guide')}}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/system-guide') ? 'bg-lime-50 text-lime-600 dark:bg-gray-700 font-bold' : 'text-gray-600 dark:text-gray-300' }}">
                                    <i class="fa-solid fa-book-bookmark text-lime-500 mr-3 text-lg"></i>
                                    <span>{{("System Use Cases Guide")}}</span>
                                </a>
                            </li>

                            <li class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{("Core Management")}}</li>

                            @canany(['manage-appointments', 'manage-doctors', 'manage-patients', 'manage-departments'])
                            <li x-data="{ open: {{ request()->is('admin/appointment*') || request()->is('admin/doctors*') || request()->is('admin/patients*') || request()->is('admin/services*') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-shutter-alt text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Appointments & Staff")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    @can('manage-appointments')
                                    <li><a href="{{url('admin/appointment')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/appointment') ? 'text-primary-600' : '' }}">{{("Appointments")}}</a></li>
                                    @endcan
                                    @can('manage-doctors')
                                    <li><a href="{{url('admin/doctors')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/doctors') ? 'text-primary-600' : '' }}">{{("Doctors")}}</a></li>
                                    @endcan
                                    @can('manage-patients')
                                    <li><a href="{{url('admin/patients')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/patients') ? 'text-primary-600' : '' }}">{{('Patients')}}</a></li>
                                    @endcan
                                    @can('manage-departments')
                                    <li><a href="{{url('admin/services')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/services') ? 'text-primary-600' : '' }}">{{("Department")}}</a></li>
                                    @endcan
                                </ul>
                            </li>
                            @endcanany
                            @can('manage-pharmacies')
                            <li x-data="{ open: {{ request()->is('admin/pharmacy*') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-medical-drip text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Pharmacy")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    <li><a href="{{url('admin/pharmacy')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/pharmacy') ? 'text-primary-600' : '' }}">{{("Pharmacies")}}</a></li>
                                    <li><a href="{{url('admin/pharmacyorder')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/pharmacyorder') ? 'text-primary-600' : '' }}">{{("Pharmacy Orders")}}</a></li>
                                </ul>
                            </li>
                            @endcan
                            @can('manage-medicines')
                            <li>
                                <a href="{{url('admin/medicines')}}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/medicines') ? 'bg-primary-50 text-primary-600 dark:bg-gray-700' : 'text-gray-600 dark:text-gray-300' }}">
                                    <i class="fas fa-pills text-primary-500 mr-3 text-lg"></i>
                                    <span>{{("Medicine")}}</span>
                                </a>
                            </li>
                            @endcan
                            @can('manage-featured')
                            <li x-data="{ open: {{ request()->is('admin/featured*') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-star text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Featured Management")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    <li><a href="{{route('admin.featured.index')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/featured') ? 'text-primary-600' : '' }}">{{("Manage Featured")}}</a></li>
                                </ul>
                            </li>
                            @endcan

                            <li class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{("Financials")}}</li>

                            @canany(['manage-subscriptions', 'manage-payments'])
                            <li x-data="{ open: {{ request()->is('admin/*_payment') || request()->is('admin/subscriptions*') || request()->is('admin/subscriber_doc') || request()->is('admin/ai-credits*') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-bill text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Subscriptions & Payments")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    @can('manage-subscriptions')
                                    <li><a href="{{route('admin.subscriptions.index')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/subscriptions*') ? 'text-primary-600' : '' }}">{{("Subscription Plans")}}</a></li>
                                    <li><a href="{{route('admin.ai_credits.index')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/ai-credits*') ? 'text-primary-600' : '' }}">{{("AI Credit Packages")}}</a></li>
                                    @endcan
                                    @can('manage-payments')
                                    <li><a href="{{url('admin/pending_payment')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/pending_payment') ? 'text-primary-600' : '' }}">{{("Pending Payments")}}</a></li>
                                    <li><a href="{{url('admin/complete_payment')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/complete_payment') ? 'text-primary-600' : '' }}">{{("Completed Payments")}}</a></li>
                                    <?php if(env('IS_FORNT')=="1") { ?>
                                        <li><a href="{{route('admin.paynow.history')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/paynow-history') ? 'text-primary-600' : '' }}">{{("Paynow History")}}</a></li>
                                    <?php } ?>
                                    @endcan
                                </ul>
                            </li>
                            @endcanany

                            @can('manage-wallets')
                            <li x-data="{ open: {{ request()->is('admin/wallet*') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-wallet text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Wallet Management")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    <li><a href="{{route('admin.wallet')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/wallet') ? 'text-primary-600' : '' }}"><span>{{("Wallet Dashboard")}}</span></a></li>
                                    <li><a href="{{ route('admin.medical_aid_providers.index') }}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/wallet/medical-aid-providers*') ? 'text-primary-600' : '' }}"><span>{{("Medical Aid Providers")}}</span></a></li>
                                    <li><a href="{{ route('admin.medical_aid_submissions.index') }}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/wallet/medical-aid-submissions*') ? 'text-primary-600' : '' }}"><span>{{("Medical Aid Submissions")}}</span></a></li>
                                    <li><a href="{{ route('admin.wallet.withdrawals') }}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/wallet/withdrawals*') ? 'text-primary-600' : '' }}"><span>{{("Withdrawal Requests")}}</span></a></li>
                                </ul>
                            </li>
                            @endcan
                            @can('manage-referrals')
                            <li x-data="{ open: {{ request()->is('admin/referrals*') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-gift text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Referral System")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    <li><a href="{{route('admin.referrals.settings')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/referrals/settings') ? 'text-primary-600' : '' }}">{{("Settings")}}</a></li>
                                    <li><a href="{{route('admin.referrals.rewards.index')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/referrals/rewards*') ? 'text-primary-600' : '' }}">{{("Manage Rewards")}}</a></li>
                                    <li><a href="{{route('admin.referrals.history')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/referrals/history') ? 'text-primary-600' : '' }}">{{("Redeemed History")}}</a></li>
                                </ul>
                            </li>
                            @endcan
                            @can('manage-reports')
                            <li x-data="{ open: {{ request()->is('admin/*_report') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-chart-line text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Reports")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    <li><a href="{{route('doctor_report')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/doctor_report') ? 'text-primary-600' : '' }}">{{("Doctors Report")}}</a></li>
                                    <li><a href="{{route('user_report')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/user_report') ? 'text-primary-600' : '' }}">{{('Patients Report')}}</a></li>
                                    <li><a href="{{route('do_sub_report')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/do_sub_report') ? 'text-primary-600' : '' }}">{{('Doctor Subscriptions Report')}}</a></li>
                                    <li><a href="{{route('app_book_report')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/app_book_report') ? 'text-primary-600' : '' }}">{{('Appointments Booked Report')}}</a></li>
                                </ul>
                            </li>
                            @endcan

                            <li class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{("Website & Comms")}}</li>

                            @can('manage-notifications')
                            <li x-data="{ open: {{ request()->is('admin/sendnotification') || request()->is('admin/notificationkey') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-bell text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Notifications")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    <li><a href="{{url('admin/sendnotification')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/sendnotification') ? 'text-primary-600' : '' }}">{{("Send Notification")}}</a></li>
                                    <li><a href="{{url('admin/notificationkey')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/notificationkey') ? 'text-primary-600' : '' }}">{{("Notification Key")}}</a></li>
                                </ul>
                            </li>
                            @endcan
                            @can('manage-website')
                            <li x-data="{ open: {{ request()->is('admin/banner') || request()->is('admin/reviews') || request()->is('admin/complain') || request()->is('admin/contact_list') || request()->is('admin/news') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-image text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Website Management")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    <li><a href="{{url('admin/banner')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/banner') ? 'text-primary-600' : '' }}">{{("Banner")}}</a></li>
                                    <li><a href="{{url('admin/reviews')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/reviews') ? 'text-primary-600' : '' }}">{{("Reviews")}}</a></li>
                                    <li><a href="{{url('admin/complain')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/complain') ? 'text-primary-600' : '' }}">{{("Complaints")}}</a></li>
                                    <?php if(env('IS_FORNT')=="1") { ?>
                                        <li><a href="{{url('admin/contact_list')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/contact_list') ? 'text-primary-600' : '' }}">{{("Contact Us")}}</a></li>
                                        <li><a href="{{url('admin/news')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/news') ? 'text-primary-600' : '' }}">{{("News")}}</a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                            @endcan
                            @can('manage-languages')
                            <li>
                                <a href="{{url('admin/languages/en')}}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/languages*') ? 'bg-primary-50 text-primary-600 dark:bg-gray-700' : 'text-gray-600 dark:text-gray-300' }}">
                                    <i class="uil-language text-primary-500 mr-3 text-lg"></i>
                                    <span>{{("Languages")}}</span>
                                </a>
                            </li>
                            @endcan
                            <li class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{("System")}}</li>

                            <li>
                                <a href="{{url('admin/developers')}}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/developers*') ? 'bg-primary-50 text-primary-600 dark:bg-gray-700' : 'text-gray-600 dark:text-gray-300' }}">
                                    <i class="uil-bug text-primary-500 mr-3 text-lg"></i>
                                    <span>{{("Developer & Logs")}}</span>
                                </a>
                            </li>

                            @canany(['edit-settings', 'manage-website'])

                             <li x-data="{ open: {{ request()->is('admin/setting') || request()->is('admin/payment-setting') || request()->is('admin/about') || request()->is('admin/Terms_condition') || request()->is('admin/app_privacy') || request()->is('admin/data_deletion') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-sliders-v-alt text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("Settings")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">

                                    @can('edit-settings')
                                    <li><a href="{{url('admin/setting')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/setting') ? 'text-primary-600' : '' }}">{{("General Settings")}}</a></li>
                                    <?php if(env('IS_FORNT')=="1") { ?>
                                        <li><a href="{{route('payment-setting')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/payment-setting') ? 'text-primary-600' : '' }}">{{("Payment Gateway Settings")}}</a></li>
                                    <?php } ?>
                                    @endcan
                                    @can('manage-website')
                                    <li><a href="{{url('admin/about')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/about') ? 'text-primary-600' : '' }}">{{("About Us")}}</a></li>
                                    <li><a href="{{url('admin/Terms_condition')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/Terms_condition') ? 'text-primary-600' : '' }}">{{("Terms & Conditions")}}</a></li>
                                    <li><a href="{{url('admin/app_privacy')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/app_privacy') ? 'text-primary-600' : '' }}">{{("Privacy Policy")}}</a></li>
                                    <li><a href="{{url('admin/data_deletion')}}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/data_deletion') ? 'text-primary-600' : '' }}">{{("Data Deletion Policy")}}</a></li>
                                    @endcan
                                </ul>
                            </li>

                            @endcanany

                            @canany(['create-admins', 'manage-roles'])
                            <li x-data="{ open: {{ request()->is('admin/admins*') || request()->is('admin/roles*') ? 'true' : 'false' }} }">
                                <a @click="open = !open" href="#" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <span class="flex items-center overflow-hidden">
                                        <i class="uil-users-alt text-primary-500 mr-3 text-lg"></i>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{("User Management")}}</span>
                                    </span>
                                    <i class="uil-angle-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </a>
                                <ul x-show="open" x-collapse class="mt-2 space-y-1 pl-9">
                                    @can('create-admins')
                                    <li><a href="{{ route('admin.admins.index') }}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/admins*') ? 'text-primary-600' : '' }}">{{("Manage Admins")}}</a></li>
                                    @endcan
                                    @can('manage-roles')
                                    <li><a href="{{ route('admin.roles.index') }}" class="block px-4 py-1.5 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('admin/roles*') ? 'text-primary-600' : '' }}">{{("Role Management")}}</a></li>
                                    @endcan
                                </ul>
                            </li>
                            @endcanany
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content flex-grow min-w-0 transition-all duration-300 ease-in-out" :class="{'lg:ml-60': sidebarOpen}">
                <div class="pt-16 pb-16"> <!-- Page content goes here, added pb-16 for footer clearance -->
                    @yield('content')
                </div>
            </div>
            <!-- end main content-->

            <footer class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all duration-300 ease-in-out" :class="{'lg:left-60': sidebarOpen}">
                <div class="container mx-auto py-4 px-6">
                    <div class="flex justify-between items-center text-sm">
                        <div class="text-gray-600 dark:text-gray-400">
                            {{date('Y')}} © Digital Vocano
                        </div>
                        <div class="hidden sm:block text-gray-600 dark:text-gray-400">
                            <i class="mdi mdi-heart text-red-500"></i> {{("by")}} <a href="https://digitalvocano.com/" target="_blank" class="text-primary-500 hover:underline">Digital Vocano</a>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
        <!-- END layout-wrapper -->

        <!-- Right bar overlay-->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden" :class="{'hidden': !sidebarOpen}" @click="sidebarOpen = false"></div>

        <!-- Hidden Inputs -->
        <input type="hidden" id="siteurl" value="{{url('admin')}}" />
        <input type="hidden" id="delete_record" value="{{('delete_record')}}">
        <input type="hidden" id="today_no_appointment_msg" value='{{("You dont have any  appointments for today")}}'/>
        <input type="hidden" id="demo" value="{{Session::get('is_demo')}}"/>
        <input type="hidden" id="soundnotify" value="{{asset('sound/notification/notification.mp3')}}" />

        <!-- App JS (includes Alpine, jQuery, etc.) -->
        <script src="{{ asset('js/app.js') }}"></script>

        <!-- Core JS -->
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/waypoints/lib/jquery.waypoints.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/jquery.counterup/jquery.counterup.min.js')}}"></script>

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

        <!-- Plugin JS -->
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/jszip/jszip.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/pdfmake/build/pdfmake.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/pdfmake/build/vfs_fonts.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/datatables.net-buttons/js/buttons.html5.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/datatables.net-buttons/js/buttons.print.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/datatables.net-buttons/js/buttons.colVis.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{asset('admin_design/layouts/vertical/assets/libs/select2/js/select2.min.js')}}"></script>
        <script src="{{url('js/locationpicker.js')}}"></script>
        <script type="text/javascript" src="{{asset('js/admin.js?v=rgtrygr')}}"></script>

        <script>
            function disablebtn(){
                alert("This Action Disable In Demo");
            }
        </script>

        @section('footer')
        <script>
            function toggleRecommendable(url, csrfToken) {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // You can add a success notification here if you like.
                        console.log(data.message);
                    } else {
                        alert('Failed to update status.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the status.');
                });
            }
        </script>
        @show
    </body>
</html>
