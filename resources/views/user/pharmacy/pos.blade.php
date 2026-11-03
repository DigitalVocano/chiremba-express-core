@extends('layouts.pharmacy')

@section('title')
    {{ __('Point of Sale') }}
@stop

@section('content')
<div class="h-screen flex flex-col" x-data="posSystem()">
    {{-- Header / Top Bar --}}
    <div class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 p-4 flex justify-between items-center shadow-sm z-10">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white"><i class="fas fa-cash-register mr-2"></i> POS Terminal</h1>
        <div class="w-1/3">
            <input type="text" x-model="search" placeholder="Search product or scan barcode..." class="w-full form-input rounded-md" @keydown.enter="scanBarcode()">
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden">
        {{-- Left Side: Product Grid --}}
        <div class="flex-1 overflow-y-auto p-4 bg-gray-100 dark:bg-gray-900">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)" class="bg-white dark:bg-gray-800 rounded-lg shadow cursor-pointer hover:shadow-lg transition-shadow overflow-hidden flex flex-col h-full">
                        <img :src="`{{ asset('upload/pharmacymedicine') }}/${product.image}`" class="w-full h-32 object-cover" onerror="this.src='{{ asset('upload/doctors/defaultpharmacy.png') }}'">
                        <div class="p-3 flex-1 flex flex-col justify-between">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200 text-sm truncate" x-text="product.name"></h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-primary-600 font-bold" x-text="'{{ $setting->currency }}' + parseFloat(product.price).toFixed(2)"></span>
                                <span class="text-xs text-gray-500" x-text="'Stock: ' + product.stock"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Right Side: Cart --}}
        <div class="w-96 bg-white dark:bg-gray-800 border-l dark:border-gray-700 flex flex-col shadow-xl z-20">
            {{-- Cart Header --}}
            <div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <h2 class="font-semibold text-lg text-gray-800 dark:text-white">Current Order</h2>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span x-text="new Date().toLocaleDateString()"></span>
                    <span x-text="'Items: ' + cartTotalItems"></span>
                </div>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto p-2 space-y-2">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                        <p>Cart is empty</p>
                    </div>
                </template>
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded border dark:border-gray-600">
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="font-medium text-gray-800 dark:text-gray-200 truncate" x-text="item.name"></p>
                            <p class="text-xs text-gray-500" x-text="'{{ $setting->currency }}' + item.price + ' x ' + item.quantity"></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="updateQty(index, -1)" class="w-6 h-6 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center"><i class="fas fa-minus text-xs"></i></button>
                            <span class="font-semibold w-4 text-center" x-text="item.quantity"></span>
                            <button @click="updateQty(index, 1)" class="w-6 h-6 rounded-full bg-green-100 text-green-600 hover:bg-green-200 flex items-center justify-center"><i class="fas fa-plus text-xs"></i></button>
                        </div>
                        <div class="ml-2 font-bold text-gray-800 dark:text-gray-200" x-text="'{{ $setting->currency }}' + (item.price * item.quantity).toFixed(2)"></div>
                    </div>
                </template>
            </div>

            {{-- Cart Footer / Checkout --}}
            <div class="p-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <div class="flex justify-between items-center mb-2 text-lg font-bold">
                    <span class="text-gray-700 dark:text-gray-300">Total</span>
                    <span class="text-primary-600" x-text="'{{ $setting->currency }}' + cartTotal.toFixed(2)"></span>
                </div>
                
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <button @click="cart = []" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded shadow text-sm font-medium">
                        <i class="fas fa-trash mr-1"></i> Clear
                    </button>
                    {{-- Placeholder for Hold/Park Order --}}
                    <button class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded shadow text-sm font-medium" disabled>
                        <i class="fas fa-pause mr-1"></i> Hold
                    </button>
                </div>

                <button @click="processCheckout()" :disabled="cart.length === 0" class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-lg font-bold text-lg flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i> Checkout
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer_scripts')
<script>
function posSystem() {
    return {
        products: @json($medicines),
        search: '',
        cart: [],
        
        get filteredProducts() {
            if (this.search === '') return this.products;
            return this.products.filter(p => 
                p.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        get cartTotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get cartTotalItems() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        addToCart(product) {
            let existing = this.cart.find(item => item.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    id: product.id,
                    product_id: product.id, // For backend API consistency
                    name: product.name,
                    price: parseFloat(product.price),
                    quantity: 1
                });
            }
        },

        updateQty(index, change) {
            if (this.cart[index].quantity + change <= 0) {
                this.cart.splice(index, 1);
            } else {
                this.cart[index].quantity += change;
            }
        },

        scanBarcode() {
            // Simple exact match simulation for barcode scanner input (usually ends with Enter)
            let product = this.products.find(p => p.name.toLowerCase() === this.search.toLowerCase()); // Replace with barcode field match if available
            if (product) {
                this.addToCart(product);
                this.search = '';
            }
        },

        processCheckout() {
            if (this.cart.length === 0) return;
            
            // Using the existing API structure for consistency
            const orderData = JSON.stringify(this.cart);
            const formData = new FormData();
            formData.append('pharmacy_id', '{{ Session::get("user_id") }}');
            formData.append('user_id', '0'); // Walk-in customer
            formData.append('total', this.cartTotal);
            formData.append('address', 'Walk-in');
            formData.append('phone', '');
            formData.append('payment_type', '1'); // Cash
            formData.append('order_data', orderData);
            
            // CSRF Token
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ url("api/medicine_order") }}', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success == "1") {
                    alert('Order Completed! Order ID: ' + data.order_id);
                    window.open(`{{ url('pharmacy/order') }}/${data.order_id}/invoice`, '_blank');
                    this.cart = [];
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
        }
    }
}
</script>
@endsection