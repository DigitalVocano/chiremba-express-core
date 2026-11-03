@extends('admin.layout')
@section('title')
{{("Edit Notification Key")}} | {{("Admin")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Edit Notification Key")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               {{("Edit Notification Key")}}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-2/3">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  @if(Session::has('message'))
                  <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                     <span>{{ Session::get('message') }}</span>
                     <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                  </div>
                  @endif
                  <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">{{("Firebase JSON Key")}}</h4>
                  <form action="{{url('admin/updatenotificationkey')}}" method="post" enctype="multipart/form-data" class="space-y-6">
                     {{csrf_field()}}
                     <div>
                        <label for="jsonfile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Upload JSON File")}}</label>
                        <input type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-gray-600 file:text-primary-700 dark:file:text-gray-200 hover:file:bg-primary-100 dark:hover:file:bg-gray-500" required id="jsonfile" name="jsonfile">
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{("Upload the serviceAccountKey.json file from your Firebase project.")}}</p>
                     </div>
                     <p class="text-sm text-gray-700 dark:text-gray-300"><b>{{("Uploaded File Name")}} :</b> <span class="font-mono bg-gray-100 dark:bg-gray-700 p-1 rounded">{{$user->not_json_filename}}</span></p>
                     <div class="flex justify-end pt-2">
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
