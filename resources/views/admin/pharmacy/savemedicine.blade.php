@extends('admin.layout')
@section('title')
{{("Save Pharmacy Medicine")}} | {{("Admin")}}
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Save Pharmacy Medicine")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{url('admin/pharmacy')}}" class="text-primary-500 hover:underline">{{("Pharmacy")}}</a>
               / {{("Save Medicine")}}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <form action="{{url('admin/updatemedicine')}}" class="space-y-6" method="post" enctype="multipart/form-data">
                     {{csrf_field()}}
                     <input type="hidden" name="pharmacy_id" id="pharmacy_id" value="{{$pharmacy_id}}">
                     <input type="hidden" name="medicine_id" id="medicine_id" value="{{$medicine_id}}">

                     <div class="mb-4">
                        <label for="upload_image" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Image")}}</label>
                        <div class="flex items-center">
                           <div class="mr-4">
                              @php
                                 $imagePath = isset($data->image) ? asset('upload/pharmacymedicine/' . $data->image) : asset('upload/pharmacymedicine/medicine.png');
                              @endphp
                              <img src="{{ $imagePath }}" alt="Medicine Image" class="h-20 w-20 object-cover rounded-md border border-gray-300 dark:border-gray-600" id="basic_img">
                              <input type="hidden" name="real_basic_img" id="real_basic_img" value="{{ $data->image ?? '' }}"/>
                           </div>
                           <div>
                              <input type="file" name="upload_image" id="upload_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700">
                           </div>
                        </div>
                     </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                           <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Name")}}<span class="text-red-500">*</span></label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder='{{("Enter")}} {{("Medicine")}} {{("Name")}}' id="name" name="name" required value="{{ $data->name ?? '' }}">
                        </div>
                        <div>
                           <label for="Price" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Price")}}<span class="text-red-500">*</span></label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="Price" placeholder='{{("Enter")}} {{("Price")}}' name="price" required value="{{ $data->price ?? '' }}">
                        </div>
                     </div>

                     <div>
                        <label for="description" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("description")}}<span class="text-red-500">*</span></label>
                        <textarea id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="5" placeholder='{{("Enter Your Description")}}' name="description" required>{{ $data->description ?? '' }}</textarea>
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
