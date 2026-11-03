@extends('admin.layout')
@section('title')
{{("save")}} {{("Doctors")}} | {{("Admin")}} {{("Doctors")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Save Doctors")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{url('admin/doctors')}}" class="text-primary-500 hover:underline">{{("Doctors")}}</a>
               / {{("Save Doctors")}}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <form action="{{url('admin/updatedoctor')}}" class="space-y-6" method="post" enctype="multipart/form-data">
                     {{csrf_field()}}
                     <input type="hidden" name="id" id="doctor_id" value="{{$id}}">

                     <div class="mb-4">
                        <label for="upload_image" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Profile Image")}}</label>
                        <div class="flex items-center">
                           <div class="mr-4">
                              @php
                              $imagePath = isset($data->image) ? asset('upload/doctors/' . $data->image) : asset('upload/profile/profile.png');
                              @endphp
                              <img src="{{ $imagePath }}" alt="Profile" class="rounded-full h-20 w-20 object-cover border border-gray-300 dark:border-gray-600" id="basic_img">
                              <input type="hidden" name="real_basic_img" id="real_basic_img" value="{{ $data->image ?? '' }}"/>
                           </div>
                           <div>
                              <input type="file" name="upload_image" id="upload_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700">
                           </div>
                        </div>
                     </div>

                     <div>
                        <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Name")}} <span class="text-red-500">*</span></label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="name" name="name" placeholder='{{("Enter Doctor Name")}}' required value="{{ $data->name ?? '' }}">
                     </div>

                     <div>
                        <label for="department_id" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Specialities")}} <span class="text-red-500">*</span></label>
                        <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" name="department_id" id="department_id" required>
                           <option value="">{{("Select Speciality")}}</option>
                           @foreach($department as $d)
                               <option value="{{$d->id}}" {{ (isset($data->department_id) && $data->department_id == $d->id) ? 'selected' : '' }}>{{$d->name}}</option>
                           @endforeach
                        </select>
                     </div>

                     <div>
                        <label for="password" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Password")}} <span class="text-red-500">*</span></label>
                        <input type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="password" name="password" placeholder='{{("Enter Password")}}' required value="{{ $data->password ?? '' }}">
                     </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                           <label for="phoneno" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Phone")}} <span class="text-red-500">*</span></label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="phoneno" name="phoneno" placeholder='{{("Enter Phone")}}' required value="{{ $data->phoneno ?? '' }}">
                        </div>
                        <div>
                           <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Email")}} <span class="text-red-500">*</span></label>
                           <input type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="email" name="email" placeholder='{{("Enter Email Address")}}' required {{ isset($id) && $id != 0 ? 'readonly' : '' }} value="{{ $data->email ?? '' }}">
                        </div>
                        <div>
                           <label for="working_time" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Working Time")}} <span class="text-red-500">*</span></label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="working_time" name="working_time" placeholder='{{("Enter Working Time")}}' required value="{{ $data->working_time ?? '' }}">
                        </div>
                        <div>
                           <label for="consultation_fees" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Consultation Fees")}} <span class="text-red-500">*</span></label>
                           <input type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="consultation_fees" name="consultation_fees" placeholder='{{("Enter Consultation Fees")}}' required value="{{ $data->consultation_fees ?? '' }}" min="1" step="0.01">
                        </div>
                     </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                           <label for="facebook_url" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Facebook URL")}}</label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="facebook_url" name="facebook_url" placeholder='{{("Enter Facebook URL")}}' value="{{ $data->facebook_url ?? '' }}">
                        </div>
                        <div>
                           <label for="twitter_url" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Twitter URL")}}</label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="twitter_url" name="twitter_url" placeholder='{{("Enter Twitter URL")}}' value="{{ $data->twitter_url ?? '' }}">
                        </div>
                     </div>

                     <div>
                        <label for="aboutus" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("About Us")}} <span class="text-red-500">*</span></label>
                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="aboutus" name="aboutus" placeholder='{{("Enter About Doctor")}}' rows="4" required>{{ $data->aboutus ?? '' }}</textarea>
                     </div>

                     <div>
                        <label for="services" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Services")}} <span class="text-red-500">*</span></label>
                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="services" name="services" placeholder='{{("Enter Description about Services")}}' rows="4" required>{{ $data->services ?? '' }}</textarea>
                     </div>

                     <div>
                        <label for="healthcare" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Health Care")}} <span class="text-red-500">*</span></label>
                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="healthcare" name="healthcare" placeholder='{{("Enter Health Care")}}' rows="4" required>{{ $data->healthcare ?? '' }}</textarea>
                     </div>

                     <div>
                        <label for="us2-address" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Address")}} <span class="text-red-500">*</span></label>
                        <input type="text" id="us2-address" name="address" placeholder='{{("Search Location")}}' required data-parsley-required="true" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" value="{{ $data->address ?? '' }}"/>
                     </div>

                     <div class="w-full h-64 rounded-md overflow-hidden" id="us2"></div>

                     <input type="hidden" name="lat" id="us2-lat" value="{{ $data->lat ?? Config::get('mapdetail.lat') }}" />
                     <input type="hidden" name="lon" id="us2-lon" value="{{ $data->lon ?? Config::get('mapdetail.long') }}" />

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
