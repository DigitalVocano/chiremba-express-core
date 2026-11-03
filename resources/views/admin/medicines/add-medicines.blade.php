@extends('admin.layout')
@section('title')
Add Medicine | Admin Dashboard
@stop
@section('content')

<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">

            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Add Medicine</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('admin.medicines.index') }}" class="text-primary-500 hover:underline">Medicines</a>
                    / Add Medicine
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                @if(Session::has('message'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                        <span>{{ Session::get('message') }}</span>
                        <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                    </div>
                @endif

                <form action="{{ route('admin.medicines.store') }}" method="post" enctype="multipart/form-data" class="space-y-6">
                    {{csrf_field()}}

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="medicinename">Medicine Name <span class="text-red-500">*</span></label>
                        <input id="medicinename" name="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="Enter Medicine Name" required>
                        @if ($errors->has('name'))
                            <span class="text-red-500 text-xs italic">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="dosage">Dosage</label>
                        <input id="dosage" name="dosage" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="e.g., 250mg, 1 tablet" required>
                        @if ($errors->has('Dosage'))
                            <span class="text-red-500 text-xs italic">{{ $errors->first('Dosage') }}</span>
                        @endif
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="description">Description</label>
                        <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="Enter a brief description" rows="3" required></textarea>
                        @if ($errors->has('description'))
                            <span class="text-red-500 text-xs italic">{{ $errors->first('description') }}</span>
                        @endif
                    </div>

                    <div>
                        <label for="showmore" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Medicine Type(s) :</label>
                        <div id="showmore" class="space-y-3">
                            <div><input type="text" name="type[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="e.g., Tablet, Capsule, Syrup" required></div>
                        </div>
                        <button type="button" class="inline-flex items-center px-4 py-2 mt-3 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150" id="addmore">Add Another Type</button>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
@section('footer')
<script>
    $(document).ready(function(){
        $('#addmore').click(function(event){
            event.preventDefault();
            $('#showmore').append('<div class="flex items-center mt-2 space-x-2"><input type="text" name="type[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="e.g., Tablet, Capsule, Syrup" required><button type="button" class="remove inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">delete</button></div>');
        });

        $('#showmore').on("click",".remove",function(e){
            e.preventDefault();
            $(this).parent('div').remove();
        });
    });
</script>
@stop
