@extends('admin.layout')

@section('title')
{{("Pharmacy Medicine")}} | {{("Admin Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Pharmacy Medicine")}}</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{url('admin/pharmacy')}}" class="text-primary-500 hover:underline">{{("Pharmacy")}}</a> / {{("Medicine")}}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                @if(Session::has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                    <div class="flex items-center">
                        <i class="uil uil-check mr-2"></i>
                        <span>{{ Session::get('message') }}</span>
                    </div>
                    <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                </div>
                @endif

                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-xl font-semibold text-gray-800 dark:text-white">{{("Medicine")}} {{("List")}}</h4>
                    <a href="{{ url('admin/addmedicine/' . $pharmacy_id . '/0') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-pills mr-2"></i>{{("Add Medicine")}}
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table id="medicineTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Image")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Name")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("description")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Price")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Action")}}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($data as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{$item->id}}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img src="{{asset('upload/pharmacymedicine/'.$item->image)}}" class="h-16 w-16 object-cover rounded-md" alt="Medicine Image">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{$item->name}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{$item->description}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{$item->price}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{url('admin/addmedicine/' . $item->pharmacy_id .'/'. $item->id) }}" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-200" title="{{('Edit')}}"><i class="uil uil-pen text-lg"></i></a>
                                        <a href="{{route('medicinedelete',$item->id)}}" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200" title="{{('Delete')}}"><i class="uil uil-trash-alt text-lg"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('footer')
<script>
    $(document).ready(function() {
        $('#medicineTable').DataTable({
            dom: 'lrtip',
            buttons: []
        });
    });
</script>
@stop
