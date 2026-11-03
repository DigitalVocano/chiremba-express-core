@extends('layouts.patient')
@section('title')
    {{__('Change Password')}}
@stop
@section('meta-data')
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{__('KumbaCare')}}"/>
    <meta property="og:title" content="{{__('Change Password')}}"/>
    <meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
    <meta property="og:image:width" content="250px"/>
    <meta property="og:image:height" content="250px"/>
    <meta property="og:site_name" content="{{__('KumbaCare')}}"/>
    <meta property="og:description" content="{{__('meta_description')}}"/>
    <meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
    <link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
    {{-- <meta name="viewport" content="width=device-width, initial-scale=1"> --}}
@stop
@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Change Password')}}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-2xl mx-auto"> {{-- Constrained width and centered --}}
        @if(Session::has('message'))
        <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-green-700 bg-green-100 dark:bg-green-200 dark:text-green-800') }} rounded-lg" role="alert">
            {{ Session::get('message') }}
        </div>
        @endif

        <div id="registererror" class="mb-4 text-sm text-red-600 dark:text-red-400"></div> {{-- For JS errors --}}

        <form action="{{url('updateuserpassword')}}" method="post" id="passwordChangeForm">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="opwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Enter Your Current Password')}}</label>
                    <input type="password" id="opwd" name="opwd" required class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm">
                    <span id="msg1" class="mt-1 text-xs text-red-500 dark:text-red-400" style="display: none;">{{__("Current_Password_wrong")}}</span>
                </div>

                <div>
                    <label for="pwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Enter Your New Password')}}</label>
                    <input type="password" name="npwd" id="pwd" required class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm">
                    <span id="msg2" class="mt-1 text-xs text-red-500 dark:text-red-400" style="display: none;">{{__("Current_Password_new_Password_same")}}</span>
                </div>

                <div>
                    <label for="cpwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Enter Confirm password')}}</label>
                    <input type="password" name="conpwd" id="cpwd" required class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm">
                    <span id="msg3" class="mt-1 text-xs text-red-500 dark:text-red-400" style="display: none;">{{__("Confirm_Password_not_match")}}</span>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end space-x-4">
                <a href="{{url('userdashboard')}}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">{{__("Cancel")}}</a>
                <button type="button" onclick="chengepswd()" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                    {{__('Save Change')}}
                </button>
            </div>
        </form>
    </div>

   <input type="hidden" id="ccpp" value="{{$userdata->password}}"> {{-- This might be insecure if password is plain text. Consider server-side validation only. --}}
@stop

@section('footer_scripts')
<script>
    function chengepswd() {
        $("#msg1").hide();
        $("#msg2").hide();
        $("#msg3").hide();
        $("#registererror").html(''); // Clear previous JS errors

        var currentPassword = $('#opwd').val();
        var newPassword = $('#pwd').val();
        var confirmPassword = $('#cpwd').val();
        // var oldCurrentPasswordHash = $('#ccpp').val(); // This is still insecure client-side.

        let isValid = true;

        if (currentPassword === '' || newPassword === '' || confirmPassword === '') {
            $("#registererror").html('{{__("All fields are required.")}}'); // Assuming you have this translation
            isValid = false;
        }

        // Server-side validation for current password is STRONGLY recommended.
        // Client-side check for old password matching is insecure.

        if (newPassword.length < 6) {
             $("#msg2").html("{{ __('New password must be at least 6 characters.') }}").show();
             isValid = false;
        } else if (currentPassword === newPassword) { // This check is fine client-side
            $("#msg2").html("{{__("Current_Password_new_Password_same")}}").show();
            isValid = false;
        }

        if (newPassword !== confirmPassword) {
            $("#msg3").html("{{__("Confirm_Password_not_match")}}").show();
            isValid = false;
        }

        if(isValid){
            $('#passwordChangeForm').submit();
        }
    }
</script>
@stop
