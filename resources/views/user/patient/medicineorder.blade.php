@extends('layouts.patient')
@section('title')
    Medicine Order
@stop
@section('meta-data')
    <meta property="og:type" content="website" />
    <meta property="og:url" content="KumbaCare" />
    <meta property="og:title" content="Medicine Order" />
    <meta property="og:image" content="{{ asset('image_web/') . '/' . $setting->favicon }}" />
    <meta property="og:image:width" content="250px" />
    <meta property="og:image:height" content="250px" />
    <meta property="og:site_name" content="KumbaCare" />
    <meta property="og:description" content="Your KumbaCare Medicine Orders" />
    <meta property="og:keyword" content="KumbaCare, Medicine, Order, Pharmacy" />
    <link rel="shortcut icon" href="{{ asset('image_web/') . '/' . $setting->favicon }}">
@stop
@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">Medicine Orders</h1>
    </div>

    <div class="space-y-6" x-data="medicineOrderManager()">
        {{-- Order Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                <div class="flex-shrink-0">
                    <div class="text-tele-dark-blue dark:text-primary-600 text-3xl">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="flex-grow ml-4 text-right">
                    <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">{{ $orderdata->total() }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Orders</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                 <div class="flex-shrink-0">
                    <div class="text-green-600 dark:text-green-400 text-3xl">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="flex-grow ml-4 text-right">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{$complet}}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Completed Orders</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                 <div class="flex-shrink-0">
                    <div class="text-yellow-600 dark:text-yellow-400 text-3xl">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="flex-grow ml-4 text-right">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{$pending}}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Pending Orders</div>
                </div>
            </div>
        </div>

        {{-- Medicine Order List --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200">Your Order History</h3>
                <a href="{{ url('searchpharmacy') }}" class="w-full sm:w-auto mt-3 sm:mt-0 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Order New Medicine <i class="fas fa-plus ml-2"></i>
                </a>
            </div>

            {{-- Desktop Table --}}
            <div class="overflow-x-auto hidden md:block mb-4">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="myTable">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Medicine Added Successfully</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Patient Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($orderdata as $bdata)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $bdata->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bdata->pharmacy->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bdata->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bdata->phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($bdata->order_type == '2') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100">Normal</span>
                                    @elseif($bdata->order_type == '1') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-700 dark:text-purple-100">Prescription</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bdata->total }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bdata->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($bdata->status == '0') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100">In Process</span>
                                    @elseif($bdata->status == '1') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Accepted</span>
                                    @elseif($bdata->status == '2') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">Rejected</span>
                                    @elseif($bdata->status == '3') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Completed</span>
                                    @elseif($bdata->status == '4') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200">Waiting</span>
                                    @elseif($bdata->status == '5') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100">Estimated</span>
                                    @else <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">Cancelled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex items-center justify-end space-x-2">
                                    <button @click="openOrderModal({{ $bdata->id }})" class="text-primary-500 hover:text-primary-600" title="View Details"><i class="fas fa-eye"></i></button>
                                   
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                    No medicine orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="grid grid-cols-1 gap-4 md:hidden">
                @forelse($orderdata as $bdata)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg shadow p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $bdata->pharmacy->name ?? 'N/A' }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Order #{{ $bdata->id }}</p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 border-t border-gray-200 dark:border-gray-600 pt-2">
                            <p><strong>Date:</strong> {{ $bdata->created_at->format('M d, Y') }}</p>
                            <p><strong>Type:</strong>
                                @if($bdata->order_type == '2') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100">Normal</span>
                                @elseif($bdata->order_type == '1') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-700 dark:text-purple-100">Prescription</span>
                                @else N/A @endif
                            </p>
                            <p><strong>Total:</strong> {{ $bdata->total }}</p>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-600">
                            <div>
                                @if($bdata->status == '0') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100">In Process</span>
                                @elseif($bdata->status == '1') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Accepted</span>
                                @elseif($bdata->status == '2') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">Rejected</span>
                                @elseif($bdata->status == '3') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Completed</span>
                                @elseif($bdata->status == '4') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200">Waiting</span>
                                @elseif($bdata->status == '5') <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100">Estimated</span>
                                @else <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">Cancelled</span>
                                @endif
                            </div>
                            <div class="text-right flex items-center space-x-2">
                               
                                <button @click="openOrderModal({{ $bdata->id }})" class="text-primary-500 hover:text-primary-600" title="View Details"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        No medicine orders found.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pagination --}}
        @if($orderdata instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6">
            {{ $orderdata->links('pagination::tailwind') }}
        </div>
        @endif

        {{-- Alpine.js Modal --}}
        <div x-show="showModal" style="display: none;" @keydown.escape.window="closeModal()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal()" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                    <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">Order Details</h3>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <span class="sr-only">Close</span><i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mt-4" id="modal_body_content">
                        {{-- Content will be injected here by JS --}}
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 focus:outline-none">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="path_admin" value="{{ url('/') }}">
    </div>
@stop

@section('footer_scripts')
    <script>
        function medicineOrderManager() {
            return {
                showModal: false,
                openOrderModal(id) {
                    this.showModal = true;
                    get_orderdata(id); // Call the existing function to populate the modal
                },
                closeModal() {
                    this.showModal = false;
                    document.getElementById('modal_body_content').innerHTML = ''; // Clear content on close
                }
            }
        }

        function get_orderdata(id) {
            const modalBody = document.getElementById('modal_body_content');
            modalBody.innerHTML = '<p class="text-center text-gray-500">Loading...</p>'; // Loading state

            $.ajax({
                url: $("#path_admin").val() + "/get_orderdata/" + id,
                type: "GET",
                dataType: 'json',
                success: function(response) {
                    const orderDetails = response.orderDetails;
                    const orderData = response.orderItems;
                    const currency = response.currency;

                    let html = `<div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">`;
                    html += `<p><b class='font-semibold'>Order ID:</b> ${orderDetails.id}</p>`;
                    html += `<p><b class='font-semibold'>Name:</b> ${orderDetails.user ? orderDetails.user.name : 'N/A'}</p>`;
                    html += `<p><b class='font-semibold'>Phone:</b> ${orderDetails.phone}</p>`;
                    html += `<p><b class='font-semibold'>Address:</b> ${orderDetails.address}</p>`;
                    html += `<p><b class='font-semibold'>Note:</b> ${orderDetails.message || 'N/A'}</p>`;
                    if (orderDetails.payment_type != null) {
                        html += `<p><b class='font-semibold'>Payment Type:</b> ${orderDetails.payment_type}</p>`;
                    }
                    html += `<p><b class='font-semibold'>Pharmacy Name:</b> ${orderDetails.pharmacy ? orderDetails.pharmacy.name : 'N/A'}</p></div><hr class='my-4 dark:border-gray-700'>`;

                    if (orderDetails.order_type == 2) {
                        var subtotal = 0;
                        html += '<div class="space-y-3">';
                        orderData.forEach(function(item) {
                            subtotal += item.qty * item.price;
                            html += `
                                <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">${item.name}</p>
                                    <div class="grid grid-cols-3 text-sm text-gray-600 dark:text-gray-400">
                                        <div>Price: ${item.price}${currency[1]}</div>
                                        <div class="text-center">Qty: ${item.qty}</div>
                                        <div class="text-right">Total: ${(item.qty * item.price).toFixed(2)}${currency[1]}</div>
                                    </div>
                                </div>`;
                        });
                        html += '</div>';

                        html += `
                            <div class="mt-4 text-right space-y-1 text-gray-700 dark:text-gray-300">
                                <p><b class="font-semibold">Sub Total:</b> ${subtotal.toFixed(2)}${currency[1]}</p>
                                <p><b class="font-semibold">Tax:</b> ${orderDetails.tax}${currency[1]}</p>
                                <p><b class="font-semibold">Delivery Charge:</b> ${orderDetails.delivery_charge}${currency[1]}</p>
                                <p class="text-lg font-bold text-tele-dark-blue dark:text-primary-600"><b class="font-semibold">Total:</b> ${orderDetails.total}${currency[1]}</p>
                            </div>`;
                    } else {
                        html += `<div class="flex justify-center"><img src="${$("#path_admin").val()}/upload/orderprescription/${orderDetails.prescription}" alt="Prescription" class="max-w-full h-auto rounded-md shadow-md"></div>`;
                    }

                    modalBody.innerHTML = html;
                },
                error: function(xhr, status, error) {
                    modalBody.innerHTML = '<p class="text-center text-red-500">Failed to load order details.</p>';
                }
            });
        }

        // DataTables initialization
        $(document).ready(function() {
            if ($.fn.DataTable) {
                try {
                    new DataTable('#myTable', {
                        order: [[0, 'desc']]
                    });
                } catch(e) {
                    console.error("Could not initialize DataTable:", e);
                }
            }
        });
    </script>
@stop
