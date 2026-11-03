@extends('admin.layout')
@section('title')
    Referral System Settings
@stop
@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div>
            <h2 class="text-2xl font-semibold leading-tight text-gray-900 dark:text-white">Referral System Settings</h2>
        </div>
        <div class="my-5">
            @if(Session::has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                    <span class="block sm:inline">{{ Session::get('message') }}</span>
                    <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                </div>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <form action="{{ route('admin.referrals.settings.update') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- System Enabled -->
                    <div class="col-span-full">
                        <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Referral System Status</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="system_enabled" value="0">
                            <input type="checkbox" name="system_enabled" value="1" class="sr-only peer" {{ ($settingsData['system_enabled']->value ?? 0) == 1 ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Enable/Disable</span>
                        </label>
                    </div>

                    <!-- Points Configuration -->
                    <div>
                        <label for="points_per_patient_referral" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Points for Referring a Patient</label>
                        <input type="number" name="points_per_patient_referral" id="points_per_patient_referral" value="{{ $settingsData['points_per_patient_referral']->value ?? 10 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                    <div>
                        <label for="points_per_doctor_referral" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Points for Referring a Doctor</label>
                        <input type="number" name="points_per_doctor_referral" id="points_per_doctor_referral" value="{{ $settingsData['points_per_doctor_referral']->value ?? 50 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                     <div>
                        <label for="points_per_pharmacy_referral" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Points for Referring a Pharmacy</label>
                        <input type="number" name="points_per_pharmacy_referral" id="points_per_pharmacy_referral" value="{{ $settingsData['points_per_pharmacy_referral']->value ?? 50 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>

                    <!-- Role Permissions -->
                    <div class="col-span-full">
                        <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Who Can Refer?</label>
                        <div class="space-y-2">
                            <label class="inline-flex items-center">
                                <input type="hidden" name="patient_can_refer" value="0">
                                <input type="checkbox" name="patient_can_refer" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" {{ ($settingsData['patient_can_refer']->value ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Patients</span>
                            </label><br>
                            <label class="inline-flex items-center">
                                <input type="hidden" name="doctor_can_refer" value="0">
                                <input type="checkbox" name="doctor_can_refer" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" {{ ($settingsData['doctor_can_refer']->value ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Doctors</span>
                            </label><br>
                             <label class="inline-flex items-center">
                                <input type="hidden" name="pharmacy_can_refer" value="0">
                                <input type="checkbox" name="pharmacy_can_refer" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" {{ ($settingsData['pharmacy_can_refer']->value ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Pharmacies</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
