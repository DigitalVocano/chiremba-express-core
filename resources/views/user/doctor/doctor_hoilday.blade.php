@extends('layouts.doctor')
@section('title')
My Holiday
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="KumbaCare"/>
<meta property="og:title" content="KumbaCare"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="KumbaCare"/>
<meta property="og:description" content="KumbaCare is a telemedication web app."/>
<meta property="og:keyword" content="telemedication, doctor, patient, pharmacy"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">My Holiday</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6" x-data="{ isModalOpen: false }">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">My Holiday List</h3>
        <button type="button" @click="isModalOpen = true" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
            <i class="fas fa-plus mr-2"></i>Add Holiday
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                           <tr>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Start Date</th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">End Date</th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                           </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                           @if(count($doctorhoilday)>0)
                              @foreach($doctorhoilday as $dh)
                                    <tr>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{$dh->start_date}}</td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{$dh->end_date}}</td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{$dh->description}}</td>
                                       <?php $delete = url('deletedoctorhoilday',array('id'=>$dh->id));?>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="delete_record('{{$delete}}')" class="text-red-500 hover:text-red-700 dark:hover:text-red-400" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                              @endforeach
                           @else
                           <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    No Holiday Data Found
                                </td>
                                    </tr>
                           @endif
                        </tbody>
                     </table>
    </div>

    {{-- AlpineJS Modal --}}
    <div x-show="isModalOpen" style="display: none;" @keydown.escape.window="isModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isModalOpen = false" aria-hidden="true"></div>

            {{-- Modal panel --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">Add My Holiday</h3>
                    <button type="button" @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mt-4">
                    <form action="{{route('post-my-hoilday')}}" method="post" id="user_address">
                       {{csrf_field()}}
                       <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                          <div class="form-group">
                             <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                             <input type="date" name="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="start_date" required="">
                          </div>
                          <div class="form-group">
                             <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                             <input type="date" name="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required="">
                          </div>
                          <div class="form-group md:col-span-2">
                             <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                             <textarea name="description" id="description" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 rows-3"></textarea>
                          </div>
                       </div>
                       <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 dark:border-gray-500">
                                Close
                            </button>
                            <button type="submit" id="address_submit_button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                                Add Holiday
                            </button>
                       </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
