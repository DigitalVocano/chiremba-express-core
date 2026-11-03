@extends('layouts.pharmacy')
@section('title')
Change Password
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
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">Change Password</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-2xl mx-auto" x-data="passwordForm()">
    @if(Session::has('message'))
    <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-blue-700 bg-blue-100 dark:bg-blue-200 dark:text-blue-800') }} rounded-lg" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <div id="registererror" class="text-red-500 mb-4"></div>

    <form action="{{url('updatepharmacypassword')}}" method="post" @submit.prevent="submitForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group">
                <label for="opwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                <input type="password" id="opwd" name="opwd" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                {{-- Server-side validation is more secure for current password check --}}
            </div>
            <div class="form-group">
                {{-- Placeholder for layout alignment --}}
            </div>
            <div class="form-group">
                <label for="pwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                <input type="password" name="npwd" id="pwd" required x-model="newPassword" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
            </div>
            <div class="form-group">
                {{-- Placeholder for layout alignment --}}
            </div>
            <div class="form-group">
                <label for="cpwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                <input type="password" name="conpwd" id="cpwd" required x-model="confirmPassword" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                <span x-show="errorMismatch" class="text-xs text-red-500 mt-1">Passwords do not match.</span>
            </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{url('pharmacychangepassword')}}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 dark:border-gray-500">Cancel</a>
            <button class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600" type="submit">
                Save Changes
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>
@stop

@section('footer_scripts')
<script>
    function passwordForm() {
        return {
            newPassword: '',
            confirmPassword: '',
            errorMismatch: false,
            
            submitForm(event) {
                this.errorMismatch = false;

                if (this.newPassword !== this.confirmPassword) {
                    this.errorMismatch = true;
                }

                if (this.errorMismatch) {
                    return; // Stop submission if there's an error
                }

                // If client-side checks pass, submit the form
                event.target.submit();
            }
        }
    }
</script>
@stop
