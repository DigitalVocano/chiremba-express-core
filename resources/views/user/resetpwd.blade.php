@extends('layouts.guest')
@section('title')
    {{__('Reset Password')}}
@stop

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-lg shadow-2xl">
        <div>
            <img class="mx-auto h-12 w-auto" src="{{ asset('image_web/' . $setting->logo) }}" alt="{{__('Chiremba Express')}}">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                {{__('Reset Password')}}
            </h2>
        </div>

        @if(isset($msg))
            <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 x-init="setTimeout(() => show = false, 5000)" class="mb-6 p-4 rounded-lg flex items-center justify-between
                     bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100"
                 role="alert">
                <span>{{ $msg }}</span>
                <button type="button" @click="show = false" class="text-current opacity-75 hover:opacity-100 focus:outline-none ml-4">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        @else
            <form action="{{url('resetnewpwd')}}" method="post" class="mt-8" x-data="passwordForm()" @submit.prevent="submitForm">
                @csrf
                <input type="hidden" name="code" value="{{$code ?? ''}}" />
                <input type="hidden" name="id" value="{{$id ?? ''}}" />
                <input type="hidden" name="type" value="{{$type ?? ''}}" />
                <div class="space-y-4"> {{-- Added spacing between form elements --}}
                    <div class="relative">
                        <label for="npwd" class="sr-only">{{__("Enter New Password")}}</label>
                        <input type="password" name="npwd" id="npwd" placeholder="{{__('Enter New Password')}}" required autocomplete="new-password"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm" x-model="newPassword">
                    </div>
                    <div class="relative">
                        <label for="rpwd" class="sr-only">{{__("Enter Re Enter New Password")}}</label>
                        <input type="password" name="rpwd" id="rpwd" placeholder="{{__('Enter Re Enter New Password')}}" required autocomplete="new-password"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm" x-model="confirmPassword">
                        <span x-show="errorMismatch" class="text-xs text-red-500 mt-1">{{__("Confirm_Password_not_match")}}</span>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                        {{__('Reset Password')}}
                    </button>
                </div>
            </form>
        @endif
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
            submitForm(event) {
                this.errorMismatch = false;

                if (this.newPassword !== this.confirmPassword) {
                    this.errorMismatch = true;
                }

                if (this.errorMismatch) {
                    return;
                }

                // If all client-side checks pass, submit the form
                event.target.submit();
            }
        }
    }
</script>
@endsection
