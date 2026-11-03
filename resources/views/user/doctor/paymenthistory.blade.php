@extends('layouts.doctor')
@section('title')
{{__('Doctor Dashboard')}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{__('KumbaCare')}}"/>
<meta property="og:title" content="{{__('KumbaCare')}}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{__('KumbaCare')}}"/>
<meta property="og:description" content="{{__('meta_description')}}"/>
<meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Payment History')}}</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                           <tr>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{__("Date")}}</th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{__("Amount")}}</th>
                           </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                          @if(count($data)>0)
                            @foreach($data as $bo)
		                           <tr>
		                              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
		                                {{$bo->date}}
		                              </td>
		                              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                        {{$bo->amount}}
		                              </td>
		                           </tr>
                            @endforeach
                           @else
                             <tr>
                                 <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                     {{__("No Data Found")}}
                                 </td>
                             </tr>
                           @endif
                        </tbody>
                     </table>
    </div>
</div>
@endsection
