@extends('layouts.doctor')
@section('title')
{{__('Change Password')}}
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
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Change Password')}}</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-2xl mx-auto">
    <div class="add-listing change-password">
        {{-- <div class="single-box"> --}}
            {{-- <div class="title-box">
                <h3>{{__('Change Password')}}</h3>
            </div> --}}
            {{-- <div class="inner-box"> --}}
                     @if(Session::has('message'))
                     <div class="col-sm-12">
                        <div class="alert  {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                           {{ Session::get('message') }}
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                           </button>
                        </div>
                     </div>
                     @endif
                     <div id="registererror" class="text-red-500 mb-4"></div>
               {{-- Note: The client-side password check was removed for security. All validation must happen on the server. --}}
               <form action="{{url('updatedoctorpassword')}}" method="post" id="passwordChangeForm" x-data="passwordForm()" @submit.prevent="submitForm">
                {{csrf_field()}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                   <div class="form-group">
                      <label for="opwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Enter Your Current Password')}}</label>
                      <input type="password"  id="opwd" name="opwd" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                      <span id="msg1" class="text-xs text-red-500 mt-1" style="display: none;">{{__("Current_Password_wrong")}}</span>
                   </div>
                   <div class="form-group">
                        {{-- Placeholder for layout --}}
                   </div>
                   <div class="form-group">
                      <label for="npwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Enter Your New Password')}}</label>
                      <input type="password" name="npwd" id="npwd" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" x-model="newPassword">
                      <span x-show="errorNewSameAsOld" class="text-xs text-red-500 mt-1">{{__("Current_Password_new_Password_same")}}</span>
                   </div>
                   <div class="form-group">
                        {{-- Placeholder for layout --}}
                   </div>
                   <div class="form-group">
                      <label for="conpwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Enter Confirm password')}}</label>
                      <input type="password" name="conpwd"  id="conpwd" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" x-model="confirmPassword">
                      <span x-show="errorMismatch" class="text-xs text-red-500 mt-1">{{__("Confirm_Password_not_match")}}</span>
                   </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{url('doctorchangepassword')}}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 dark:border-gray-500">{{__("Cancel")}}</a>
                    <button class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600" type="submit">
                        {{__('Save Change')}}
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
                </form>
    </div>
</div>
@endsection

@section('footer_scripts')
<script>
    function passwordForm() {
        return {
            newPassword: '',
            confirmPassword: '',
            errorMismatch: false,
            errorNewSameAsOld: false,
            submitForm(event) {
                this.errorMismatch = false;
                this.errorNewSameAsOld = false;

                const oldPassword = document.getElementById('opwd').value;

                if (this.newPassword && this.newPassword === oldPassword) {
                    this.errorNewSameAsOld = true;
                }

                if (this.newPassword !== this.confirmPassword) {
                    this.errorMismatch = true;
                }

                if (this.errorMismatch || this.errorNewSameAsOld) {
                    return;
                }

                // If all client-side checks pass, submit the form
                event.target.submit();
            }
        }
    }
</script>
@endsection
