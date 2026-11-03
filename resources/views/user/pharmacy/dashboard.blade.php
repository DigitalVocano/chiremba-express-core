@extends('layouts.pharmacy')
@section('title')
    Dashboard
@endsection
@section('meta-data')
    <meta property="og:type" content="website" />
    <meta property="og:url" content="KumbaCare" />
    <meta property="og:title" content="KumbaCare" />
    <meta property="og:image" content="{{ asset('image_web/') . '/' . $setting->favicon }}" />
    <meta property="og:site_name" content="KumbaCare" />
    <meta property="og:description" content="KumbaCare is a telemedication web app." />
    <meta property="og:keyword" content="telemedication, doctor, patient, pharmacy" />
@endsection
@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">Pharmacy Dashboard</h1> 
    </div>

    {{-- This is the main Alpine component wrapper. It now wraps EVERYTHING. --}}
    <div x-data="pharmacyDashboard()">
        <div class="space-y-6">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="text-tele-dark-blue dark:text-primary-600 text-3xl"> 
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                    <div class="flex-grow ml-4 text-right">
                        <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">{{ count($orderdata) }}</div> 
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Orders</div> 
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="text-yellow-500 dark:text-yellow-400 text-3xl">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="flex-grow ml-4 text-right">
                        <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">{{ $totalreview }}</div> 
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Reviews</div> 
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="text-green-500 dark:text-green-400 text-3xl">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="flex-grow ml-4 text-right">
                        <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">{{ $today }}</div> 
                        <div class="text-sm text-gray-600 dark:text-gray-400">Today's Orders</div> 
                    </div>
                </div>
                {{-- Wallet Balance Card (assuming pharmacies also have wallets) --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="text-purple-500 dark:text-purple-400 text-3xl">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <div class="flex-grow ml-4 text-right">
                        <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">
                            {{ $setting->currency }}{{ number_format($doctordata->wallet->balance ?? 0, 2) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{__('Wallet Balance')}}</div>
                    </div>
                </div>
            </div>

            {{-- Medicine Order List --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 sm:p-6"> 
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200">Medicine Orders</h3>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="myTable">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Patient Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($orderdata as $bdata)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $bdata->id }}</td> 
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bdata->user_id }} </td> 
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bdata->phone }}</td> 
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 capitalize">
                                        @if ($bdata->order_type == '2') 
                                            <span>Normal</span>
                                        @elseif ($bdata->order_type == '1')
                                            <span>Prescription</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $setting->currency }}{{ number_format($bdata->total, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">{{ \Carbon\Carbon::parse($bdata->created_at)->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                        $statusClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                        $statusText = 'Unknown';
                                        if ($bdata->status == '0') {
                                            $statusText = 'In Process'; $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100';
                                        } elseif ($bdata->status == '1') {
                                            $statusText = 'Accepted'; $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100';
                                        } elseif ($bdata->status == '2') {
                                            $statusText = 'Rejected'; $statusClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100';
                                        } elseif ($bdata->status == '3') {
                                            $statusText = 'Completed'; $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100';
                                        } elseif ($bdata->status == '4') {
                                            $statusText = 'Waiting'; $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100';
                                        } elseif ($bdata->status == '5') {
                                            $statusText = 'Estimated'; $statusClass = 'bg-purple-100 text-purple-800 dark:bg-purple-700 dark:text-purple-100';
                                        } else { // Assuming status 6 is 'cancel'
                                            $statusText = 'Cancelled'; $statusClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100';
                                        }
                                        @endphp
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">{{ $statusText }}</span> 
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button type="button"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition"
                                                x-on:click="openOrderDetails({{ $bdata->id }}, '{{ json_encode($bdata) }}')"
                                                title="More Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if ($bdata->status == '0')
                                                <a href="{{ url('pharmacyorderchangestatus/' . $bdata->id . '/' . '1') }}"
                                                    class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition" title="Accept"><i class="fas fa-check-circle"></i></a>
                                                <a href="{{ url('pharmacyorderchangestatus/' . $bdata->id . '/' . '2') }}"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition" title="Reject"><i class="fas fa-times-circle"></i></a>
                                            @elseif ($bdata->status == '1' && $bdata->order_type == '2')
                                                <a href="{{ url('pharmacyorderchangestatus/' . $bdata->id . '/' . '3') }}"
                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition" title="Mark as Completed"><i class="fas fa-truck"></i></a>
                                            @elseif ($bdata->status == '4' && $bdata->order_type == '1')
                                                <a href="{{ url('pharmacyorderchangestatus/' . $bdata->id . '/' . '3') }}"
                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition" title="Mark as Completed"><i class="fas fa-truck"></i></a>
                                            @elseif ($bdata->status == '1' && $bdata->order_type == '1')
                                                <button type="button"
                                                    x-on:click="showAddPriceModal = true; currentOrderIdForPrice = {{ $bdata->id }};"
                                                    class="text-yellow-500 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300 transition" title="Add Price"> 
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Add Price Modal (Alpine.js & Tailwind) --}}
        <div x-show="showAddPriceModal" style="display: none;" @keydown.escape.window="showAddPriceModal = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"> 
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showAddPriceModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="showAddPriceModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showAddPriceModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg"> 
                    <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">Add Price</h3> 
                        <button type="button" @click="showAddPriceModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <span class="sr-only">Close</span> <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mt-4">
                        <form action="{{ url('addprice') }}" method="post">
                            @csrf
                            <input type="hidden" name="id" :value="currentOrderIdForPrice">
                            <div> 
                                <label for="price_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                                <input type="text" name="price" id="price_input" x-model="price" placeholder="Add Price" class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm" required>
                            </div>
                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" @click="showAddPriceModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 focus:outline-none">Cancel</button> 
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Details Modal (Alpine.js & Tailwind) --}}
        <div x-show="showOrderDetailsModal" style="display: none;" @keydown.escape.window="showOrderDetailsModal = false"
            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="orderDetailsModalTitle" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showOrderDetailsModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="showOrderDetailsModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showOrderDetailsModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg"> 
                    <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="orderDetailsModalTitle">Order Details</h3> 
                        <button type="button" @click="showOrderDetailsModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <span class="sr-only">Close</span> <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mt-4 space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        <template x-if="currentOrderDetails">
                            <div>
                                <p><strong class="font-semibold">Order ID:</strong> <span x-text="currentOrderDetails.id"></span></p> 
                                <p><strong class="font-semibold">Patient Name:</strong> <span x-text="currentOrderDetails.user_id"></span></p>
                                <p><strong class="font-semibold">Phone:</strong> <span x-text="currentOrderDetails.phone"></span></p>
                                <p><strong class="font-semibold">Address:</strong> <span x-text="currentOrderDetails.address"></span></p>
                                <p><strong class="font-semibold">Note:</strong> <span x-text="currentOrderDetails.message"></span></p>
                                <p x-show="currentOrderDetails.payment_type"><strong class="font-semibold">Payment Type:</strong> <span x-text="currentOrderDetails.payment_type"></span></p>
                                <hr class="my-3 dark:border-gray-700">

                                <template x-if="currentOrderDetails.order_type == '2' && orderItems.length > 0">
                                    <div class="space-y-2">
                                        <h4 class="font-semibold text-md text-gray-800 dark:text-gray-200">Items:</h4> 
                                        <template x-for="item in orderItems" :key="item.id">
                                            <div class="p-3 border rounded-md dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                                <p class="font-medium" x-text="item.name"></p>
                                                <div class="grid grid-cols-3">
                                                    <span>Price: <span x-text="currencySymbol + item.price"></span></span> 
                                                    <span class="text-center">Qty: <span x-text="item.qty"></span></span> 
                                                    <span class="text-right">Total: <span x-text="currencySymbol + ((parseFloat(item.qty) * parseFloat(item.price)).toFixed(2))"></span></span>
                                                </div>
                                            </div>
                                        </template>
                                        <div class="mt-3 pt-3 border-t dark:border-gray-700 text-right space-y-1">
                                            <p>Sub Total: <span x-text="currencySymbol + calculateSubtotal()"></span></p>
                                            <p>Tax: <span x-text="currencySymbol + currentOrderDetails.tax"></span></p>
                                            <p>Delivery Charge: <span x-text="currencySymbol + currentOrderDetails.delivery_charge"></span></p>
                                            <p class="font-bold text-lg">Total: <span x-text="currencySymbol + currentOrderDetails.total"></span></p> 
                                        </div>
                                    </div>
                                </template>
                                <template x-if="currentOrderDetails.order_type == '1'">
                                    <div>
                                        <h4 class="font-semibold text-md text-gray-800 dark:text-gray-200 mb-2">Prescription:</h4> 
                                        <img :src="'{{ asset('upload/orderprescription') }}/' + currentOrderDetails.prescription" alt="Prescription" class="max-w-full h-auto rounded-md shadow">
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="showOrderDetailsModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 focus:outline-none">
                            Close 
                        </button>
                        <a :href="'{{ url('pharmacy/order') }}/' + currentOrderDetails.id + '/invoice'" target="_blank" class="ml-3 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none">
                            <i class="fas fa-file-pdf mr-2"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="path_admin" value="{{ url('/') }}"> 
@endsection
@section('footer_scripts')
    <script>
        function pharmacyDashboard() {
            return {
                showAddPriceModal: false,
                currentOrderIdForPrice: null,
                price: '',
                showOrderDetailsModal: false,
                currentOrderDetails: null,
                orderItems: [],
                currencySymbol: '{{ $setting->currency ?? '$' }}',
                
                init() {
                    // DataTables initialization is now handled in $(document).ready()
                    // to ensure jQuery and DataTables are fully loaded.
                },
                
                calculateSubtotal() {
                    if (!this.orderItems || this.orderItems.length === 0) return '0.00';
                    return this.orderItems.reduce((sum, item) => sum + (parseFloat(item.qty) * parseFloat(item.price)), 0).toFixed(2);
                },

                openOrderDetails(orderId, orderDetailsJson) {
                    this.currentOrderDetails = JSON.parse(orderDetailsJson);
                    this.showOrderDetailsModal = true;
                    this.fetchOrderItems(orderId);
                },

                fetchOrderItems(orderId) {
                    this.orderItems = []; // Clear previous items
                    $.ajax({
                        url: $("#path_admin").val() + "/get_orderdata/" + orderId,
                        data: {},
                        success: (data) => {
                            this.orderItems = data[0] || [];
                            this.currencySymbol = data[2] && data[2].length > 1 ? data[2][1] : '{{ $setting->currency ?? '$' }}';
                        },
                        error: (xhr, status, error) => {
                            console.error("Error fetching order items:", error);
                        }
                    });
                }
            };
        }

        // Initialize DataTables after the DOM is ready and all scripts are loaded
        $(document).ready(function() {
            $('#myTable').DataTable({
                order: [[0, 'desc']]
            });
        });
    </script>
@endsection
