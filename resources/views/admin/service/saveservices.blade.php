@extends('admin.layout')
@section('title')
{{ $id == 0 ? 'Add Speciality' : 'Edit Speciality' }} | Admin
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $id == 0 ? 'Add Speciality' : 'Edit Speciality' }}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{ route('admin.services.index') }}" class="text-primary-500 hover:underline">Specialities</a>
               / {{ $id == 0 ? 'Add' : 'Edit' }}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-1/2">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <form action="{{ route('admin.services.update') }}" method="post" enctype="multipart/form-data" class="space-y-6">
                     {{csrf_field()}}
                     <input type="hidden" name="id" value="{{$id}}">

                     <div>
                        <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="name" name="name" placeholder="Enter Speciality Name" value="{{ $data->name ?? '' }}" required>
                     </div>

                     <div>
                        <label for="icon" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Icon @if($id == 0)<span class="text-red-500">*</span>@endif</label>
                        @if(isset($data) && $data->icon)
                        <div class="mb-2">
                           <img src="{{asset('upload/services').'/'.$data->icon}}" class="h-24 w-24 object-contain rounded-md border border-gray-300 dark:border-gray-600 p-1" alt="Service Icon" />
                        </div>
                        <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700" id="icon" name="icon">
                        @else
                        <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700" required id="icon" name="icon">
                        @endif
                     </div>

                     <div>
                        <label for="description" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Description</label>
                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="description" name="description" placeholder="Enter a brief description" rows="3">{{ $data->description ?? '' }}</textarea>
                     </div>

                     <div class="flex justify-end">
                        @if(Session::get("is_demo")=='0')
                           <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">Submit</button>
                        @else
                           <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">Submit</button>
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
