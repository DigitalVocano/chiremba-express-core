@extends('admin.layout')

@section('title')
{{("About Us")}} | {{("Admin Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
  <div class="page-content p-6">
    <div class="container-fluid mx-auto">
      <!-- Page Title -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("About Us")}}</h1>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ url('admin/dashboard') }}" class="text-primary-500 hover:underline">{{("Dashboard")}}</a>
            / {{("About Us")}}
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

            <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">{{("About Us Page Content")}}</h4>

            <div class="space-y-6">
              <div class="flex justify-between items-center mb-4">
                <h6 class="text-lg font-medium text-gray-900 dark:text-white">{{url('aboutus')}}</h6>
                <a href="{{url('aboutus')}}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150" target="_blank">{{("Visit")}}</a>
              </div>

              <form action="{{url('admin/edit_about')}}" method="post" enctype="multipart/form-data" class="space-y-6">
                {{csrf_field()}}
                <div>
                  <input type="hidden" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="id" name="id" value="{{isset($data->id)?$data->id:0}}">
                  <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" name="about">{{isset($data->about)?$data->about:''}}</textarea>
                </div>
                <div class="flex justify-end">
                  <button name="update_about" type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Submit")}}</button>
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
<script src="{{asset('ckeditor/ckeditor.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        CKEDITOR.replace('about');
    });
</script>
@stop
