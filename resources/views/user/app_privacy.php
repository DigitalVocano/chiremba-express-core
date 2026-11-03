@extends('user.layout') {{-- Assuming 'user.layout' is your base layout that includes Tailwind, Alpine.js, and dark mode setup --}}

@section('title')
Privacy Policy | Admin Dashboard
@stop

@section('meta-data')
{{-- No specific meta data needed for an admin dashboard internal page --}}
@stop

@section('content')

<div class="main-content flex-grow py-8 px-4 sm:px-6 lg:px-8 bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
    <div class="container mx-auto">
        <div class="flex justify-center">
            <div class="w-full lg:w-4/5 xl:w-3/4"> {{-- Adjust width for better readability on large screens --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sm:p-8">

                    {{-- Session Messages --}}
                    @if(Session::has('message'))
                        <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             x-init="setTimeout(() => show = false, 5000)" class="mb-6 p-4 rounded-lg flex items-center justify-between
                                 @if(Session::get('alert-class', 'alert-info') == 'alert-success')
                                     bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                 @elseif(Session::get('alert-class') == 'alert-danger')
                                     bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                 @else
                                     bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                 @endif"
                             role="alert">
                            <span>{{ Session::get('message') }}</span>
                            <button type="button" @click="show = false" class="text-current opacity-75 hover:opacity-100 focus:outline-none ml-4">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                    @endif

                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6 text-center">
                        {{__('Privacy Policy Editor')}}
                    </h2>

                    <div class="mt-8">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                            <h5 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                {{__('Edit Privacy Policy Content')}}
                            </h5>
                            <form action="{{url('admin/edit_privecy')}}" method="post" enctype="multipart/form-data">
                                @csrf {{-- Laravel CSRF token --}}

                                <div class="mb-6">
                                    <label for="desctiption" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{__('Privacy Policy Content')}}
                                    </label>
                                    
                                    {{-- Hidden ID field --}}
                                    <input type="hidden" id="id" name="id" value="{{isset($data->id) ? $data->id : 0}}">

                                    {{-- CKEditor Textarea --}}
                                    <textarea id="desctiption" name="desctiption"
                                              class="block w-full rounded-md border-gray-300 shadow-sm
                                                     focus:border-primary-500 focus:ring-primary-500
                                                     dark:bg-gray-700 dark:border-gray-600 dark:text-white
                                                     @error('desctiption') border-red-500 @enderror"
                                              rows="15">{{isset($data->desctiption) ? $data->desctiption : ''}}</textarea>
                                    @error('desctiption')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <button type="submit" name="update_privecy"
                                        class="w-full sm:w-auto inline-flex justify-center py-3 px-6 border border-transparent rounded-md shadow-sm text-lg font-medium text-white
                                               bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
                                               dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out">
                                    {{__('Update Policy')}}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- CKEditor and jQuery scripts --}}
<script src="{{asset('js/vendor/jquery-2.1.4.min.js')}}"></script> {{-- Keep jQuery if CKEditor needs it --}}
<script src="{{asset('ckeditor/ckeditor.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // Ensure the textarea with id 'desctiption' is targeted for CKEditor
        CKEDITOR.replace('desctiption');
    });
</script>
@stop

@section('footer')
{{-- Any specific footer content or scripts for this page --}}
@stop
