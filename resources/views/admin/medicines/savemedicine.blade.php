@extends('admin.layout')
@section('title')
{{("Save Medicine")}} | {{("Admin Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Save Medicine")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{url('admin/medicines')}}" class="text-primary-500 hover:underline">{{("Medicines")}}</a>
               / {{("Save Medicine")}}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-1/2">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <form x-data="{}" action="{{url('admin/addmedicinessave')}}" method="post" enctype="multipart/form-data" class="space-y-6">
                     {{csrf_field()}}
                     <input type="hidden" name="id" value="{{isset($id)?$id:0}}">

                     <div>
                        <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Name")}}<span class="text-red-500">*</span></label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="name" name="name" placeholder='{{("Enter Medicine Name")}}' value="{{isset($data)?$data->name:''}}" required>
                     </div>

                     <div class="flex justify-end">
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
@stop
