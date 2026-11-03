@extends('admin.layout')
@section('title')
{{("Change Password")}} | {{("Admin Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Change Password")}}</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ url('admin/dashboard') }}" class="text-primary-500 hover:underline">{{("Dashboard")}}</a>
                    / {{("Change Password")}}
                </div>
            </div>

            <div class="flex justify-center">
                <div class="w-full lg:w-2/3">
                    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                        @if(Session::has('message'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                            <span>{{ Session::get('message') }}</span>
                            <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                        </div>
                        @endif
                        <form x-data="passwordForm()" action="{{url('admin/updatepassword')}}" method="post" class="space-y-6">
                            {{csrf_field()}}
                            <div>
                                <label for="currentpwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Enter Your Current Password")}}</label>
                                <input type="password" class="mt-1 block w-full rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" :class="currentPasswordError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500'" id="currentpwd" name="currentpwd" placeholder='{{("Enter Your Current Password")}}' required x-model="currentPassword" @blur="validateCurrentPassword()">
                                <p x-show="currentPasswordError" x-text="currentPasswordError" class="mt-2 text-sm text-red-600"></p>
                            </div>
                            <div>
                                <label for="newpwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Enter New Password")}}</label>
                                <input type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="newpwd" name="newpwd" placeholder='{{("Enter New Password")}}' required x-model="newPassword" @input="validateConfirmPassword()">
                            </div>
                            <div>
                                <label for="repwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Re Enter New Password")}}</label>
                                <input type="password" class="mt-1 block w-full rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" :class="confirmPasswordError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500'" id="repwd" name="repwd" placeholder='{{("Re Enter New Password")}}' required x-model="confirmPassword" @input="validateConfirmPassword()">
                                <p x-show="confirmPasswordError" x-text="confirmPasswordError" class="mt-2 text-sm text-red-600"></p>
                            </div>
                            <div class="flex justify-end pt-4">
                                @if(Session::get("is_demo")=='0')
                                <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{('Submit')}}</button>
                                @else
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-50 transition ease-in-out duration-150" :disabled="!canSubmit()">{{("Submit")}}</button>
                                @endif
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
<script>
    function passwordForm() {
        return {
            currentPassword: '',
            newPassword: '',
            confirmPassword: '',
            currentPasswordError: '',
            confirmPasswordError: '',
            isChecking: false,
            validateCurrentPassword() {
                if (!this.currentPassword) {
                    this.currentPasswordError = '';
                    return;
                }
                this.isChecking = true;
                this.currentPasswordError = '';
                fetch(`{{url("admin/check_password_same")}}/${this.currentPassword}`)
                    .then(response => response.text())
                    .then(data => {
                        if (data == '0') { // Note: server returns '0' for wrong password
                            this.currentPasswordError = '{{("Current Password is Wrong")}}';
                        }
                    })
                    .finally(() => {
                        this.isChecking = false;
                    });
            },
            validateConfirmPassword() {
                if (this.newPassword && this.confirmPassword && this.newPassword !== this.confirmPassword) {
                    this.confirmPasswordError = '{{("Password And Confirm Password Must Be Same")}}';
                } else {
                    this.confirmPasswordError = '';
                }
            },
            canSubmit() {
                return this.currentPassword && this.newPassword && this.confirmPassword && !this.currentPasswordError && !this.confirmPasswordError;
            }
        }
    }
</script>
@stop
