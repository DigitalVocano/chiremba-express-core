@extends('admin.layout')
@section('title')
{{("Payment Gateway Setting")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Payment Gateway Setting")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               {{("Payment Gateway Setting")}}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  @if(Session::has('message'))
                  <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                     <span>{{ Session::get('message') }}</span>
                     <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                  </div>
                  @endif

                  <div x-data="{ activeTab: 'paynow' }" class="flex flex-col md:flex-row">
                     <!-- Tabs Navigation -->
                     <div class="md:w-1/4 mb-4 md:mb-0">
                        <ul class="flex flex-col space-y-1">
                           <li>
                              <a @click.prevent="activeTab = 'paynow'" href="#" :class="{ 'bg-primary-600 text-white': activeTab === 'paynow', 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': activeTab !== 'paynow' }" class="block px-4 py-3 rounded-md font-medium transition-colors duration-200">{{("Paynow")}}</a>
                           </li>
                           <li>
                              <a @click.prevent="activeTab = 'ecocash'" href="#" :class="{ 'bg-primary-600 text-white': activeTab === 'ecocash', 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': activeTab !== 'ecocash' }" class="block px-4 py-3 rounded-md font-medium transition-colors duration-200">{{("EcoCash")}}</a>
                           </li>
                        </ul>
                     </div>

                     <!-- Tabs Content -->
                     <div class="md:w-3/4 md:pl-8">
                        <!-- Paynow Tab -->
                        <div x-show="activeTab === 'paynow'" class="space-y-6">
                           <form action="{{route('updategateway')}}" method="post">
                              {{csrf_field()}}
                              <input type="hidden" name="payment_gateway" value="paynow">
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div>
                                    <label for="integration_id" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Integration ID")}}<span class="text-red-500">*</span></label>
                                    <input type="text" id="integration_id" name="integration_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required placeholder="{{('Enter Paynow Integration ID')}}" value="{{isset($arr['paynow_integration_id'])?$arr['paynow_integration_id']:''}}">
                                 </div>
                                 <div>
                                    <label for="integration_key" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Integration Key")}}<span class="text-red-500">*</span></label>
                                    <input type="text" id="integration_key" name="integration_key" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required placeholder="{{('Enter Paynow Integration Key')}}" value="{{isset($arr['paynow_integration_key'])?$arr['paynow_integration_key']:''}}">
                                 </div>
                              </div>
                              <div class="mt-6">
                                 <label for="is_active_paynow" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Is Payment Gateway Is Active")}}<span class="text-red-500">*</span></label>
                                 <select name="is_active" id="is_active_paynow" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required>
                                  <option value="1" <?= isset($arr['paynow_is_active'])&&$arr['paynow_is_active']=='1'?"selected='selected'":''?>>{{("Yes")}}</option>
                                  <option value="0" <?= isset($arr['paynow_is_active'])&&$arr['paynow_is_active']=='0'?"selected='selected'":''?> >{{("No")}}</option>
                                 </select>
                              </div>
                              <div class="flex justify-end mt-6">
                                 <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Submit")}}</button>
                              </div>
                           </form>
                        </div>

                        <!-- EcoCash Tab -->
                        <div x-show="activeTab === 'ecocash'" class="space-y-6" style="display: none;">
                           <form action="{{route('updategateway')}}" method="post">
                              {{csrf_field()}}
                              <input type="hidden" name="payment_gateway" value="ecocash">
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div>
                                    <label for="ecocash_username" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("API Username")}}<span class="text-red-500">*</span></label>
                                    <input type="text" id="ecocash_username" name="username" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{isset($arr['ecocash_username'])?$arr['ecocash_username']:''}}">
                                 </div>
                                 <div>
                                    <label for="ecocash_password" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("API Password")}}<span class="text-red-500">*</span></label>
                                    <input type="text" id="ecocash_password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{isset($arr['ecocash_password'])?$arr['ecocash_password']:''}}">
                                 </div>
                                 <div>
                                    <label for="ecocash_merchant_code" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Merchant Code")}}<span class="text-red-500">*</span></label>
                                    <input type="text" id="ecocash_merchant_code" name="merchant_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{isset($arr['ecocash_merchant_code'])?$arr['ecocash_merchant_code']:''}}">
                                 </div>
                                 <div>
                                    <label for="ecocash_merchant_pin" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Merchant PIN")}}<span class="text-red-500">*</span></label>
                                    <input type="text" id="ecocash_merchant_pin" name="merchant_pin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{isset($arr['ecocash_merchant_pin'])?$arr['ecocash_merchant_pin']:''}}">
                                 </div>
                                 <div>
                                    <label for="ecocash_merchant_number" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Merchant Number")}}<span class="text-red-500">*</span></label>
                                    <input type="text" id="ecocash_merchant_number" name="merchant_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{isset($arr['ecocash_merchant_number'])?$arr['ecocash_merchant_number']:''}}">
                                 </div>
                                 <div>
                                    <label for="ecocash_endpoint" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("API Endpoint")}}<span class="text-red-500">*</span></label>
                                    <input type="text" id="ecocash_endpoint" name="endpoint" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{isset($arr['ecocash_endpoint'])?$arr['ecocash_endpoint']:'https://payonline.econet.co.zw/ecocashGateway-preprod/payment/v1/transactions/amount'}}">
                                 </div>
                              </div>
                              <div class="mt-6">
                                 <label for="is_active_ecocash" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Is Payment Gateway Is Active")}}<span class="text-red-500">*</span></label>
                                 <select name="is_active" id="is_active_ecocash" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required>
                                  <option value="1" <?= isset($arr['ecocash_is_active'])&&$arr['ecocash_is_active']=='1'?"selected='selected'":''?>>{{("Yes")}}</option>
                                  <option value="0" <?= isset($arr['ecocash_is_active'])&&$arr['ecocash_is_active']=='0'?"selected='selected'":''?> >{{("No")}}</option>
                                 </select>
                              </div>
                              <div class="flex justify-end mt-6">
                                 <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Submit")}}</button>
                              </div>
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
@stop
