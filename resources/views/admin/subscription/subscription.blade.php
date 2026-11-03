@extends('admin.layout')
@section('title')
{{("Subscription Plans")}} | {{("Admin")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Subscription Plans")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               {{("Subscription Plans")}}
            </div>
         </div>

         <script>
            // Define the Alpine component before the HTML that uses it is parsed.
            // This resolves the "subscriptionManager is not defined" error.
            document.addEventListener('alpine:init', () => {
               Alpine.data('subscriptionManager', () => ({
                  modalOpen: false,
                  modalTitle: '',
                  formAction: '',
                  plan: { id: null, name: '', price: '', month: '' },
                  
                  openAddModal() {
                     this.resetForm();
                     this.modalOpen = true;
                     this.modalTitle = '{{ __("Add New Subscription Plan") }}';
                     this.formAction = '{{ url("admin/add_subscription_price") }}';
                  },
                  
                  openEditModal(planData) {
                     this.plan = { ...planData };
                     this.modalOpen = true;
                     this.modalTitle = `{{ __("Edit Subscription Plan") }}: ${planData.name}`;
                     this.formAction = '{{ url("admin/update_subscriptio_price") }}';
                  },
                  
                  closeModal() {
                     this.modalOpen = false;
                     this.resetForm();
                  },
                  
                  resetForm() {
                     this.plan = { id: null, name: '', price: '', month: '' };
                     this.modalTitle = '';
                     this.formAction = '';
                  }
               }));
            });
         </script>

         <div x-data="subscriptionManager" @open-edit-modal.window="openEditModal($event.detail)">
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            @if(Session::has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
               <div class="flex items-center">
                   <i class="uil uil-check mr-2"></i>
                   <span>{{ Session::get('message') }}</span>
               </div>
               <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
            </div>
            @endif

            <div class="flex justify-between items-center mb-4">
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white">{{("Subscription Plans List")}}</h4>
                <button @click="openAddModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Add Plan")}}</button>
            </div>

            <div class="overflow-x-auto">
               <table id="subscriptiontable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                     <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Name")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Price")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Month")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Action")}}</th>
                     </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                     {{-- DataTables will populate this tbody --}}
                  </tbody>
               </table>
            </div>
            </div>

            <!-- Add/Edit Subscription Modal -->
            <div x-show="modalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-cloak>
               <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-800">
                  <div class="flex justify-between items-center pb-3">
                     <h5 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="modalTitle"></h5>
                     <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold">&times;</button>
                  </div>
                  <form :action="formAction" method="post" class="space-y-6">
                     {{csrf_field()}}
                     <input type="hidden" name="id" :value="plan.id">

                     <div>
                        <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Name")}}<span class="text-red-500">*</span></label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="name" name="name" placeholder='{{("Enter Plan Name")}}' x-model="plan.name" required>
                     </div>

                     <div>
                        <label for="price" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Price")}}<span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="price" name="price" placeholder='{{("Enter Price")}}' x-model="plan.price" required>
                     </div>

                     <div>
                        <label for="month" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Month")}}<span class="text-red-500">*</span></label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="month" name="month" placeholder='{{("Enter Duration in months (e.g., 1, 3, 12)")}}' x-model="plan.month" required>
                     </div>

                     <div class="flex justify-end pt-4">
                        <button type="button" @click="closeModal()" class="inline-flex items-center mr-2 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">{{('Cancel')}}</button>
                        @if(Session::get("is_demo")=='0')
                           <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{('Submit')}}</button>
                        @else
                           <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Submit")}}</button>
                        @endif
                     </div>
                  </form>
               </div>
            </div>

         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
<script>
   // Initialize DataTables after the DOM is ready.
   $(document).ready(function() {
      $('#subscriptiontable').DataTable({
         destroy: true, // This helps prevent reinitialization errors
         processing: true,
         serverSide: true,
         ajax: '{{ url("admin/subscriptiontable") }}',
         columns: [
               { data: 'id', name: 'id' },
               { data: 'name', name: 'name' },
               { data: 'price', name: 'price' },
               { data: 'month', name: 'month' },
               {
                  data: 'id',
                  name: 'action',
                  orderable: false,
                  searchable: false,
                  render: function(data, type, row) {
                     const editButton = `<button type="button" @click='$dispatch("open-edit-modal", ${JSON.stringify(row)})' class="p-2 rounded-full text-blue-600 hover:bg-blue-100 dark:hover:bg-gray-700 text-xl" title="Edit"><i class="uil uil-pen"></i></button>`;
                     const deleteButton = `<a href="javascript:void(0);" onclick="delete_record('${row.id}', '{{ url('admin/deletesubscription') }}/${row.id}')" class="p-2 rounded-full text-red-600 hover:bg-red-100 dark:hover:bg-gray-700 text-xl" title="Delete"><i class="uil uil-trash-alt"></i></a>`;
                     return `<div class="flex items-center space-x-2">${editButton}${deleteButton}</div>`;
                  }
               }
         ],
         drawCallback: function(settings) {
               if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                  window.Alpine.initTree(this.api().table().body());
               }
         },
         dom: '<"flex justify-between items-center mb-4"l<"ml-auto"f>>rtip'
      });
   });
</script>
@stop
