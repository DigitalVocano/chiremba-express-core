@extends('admin.layout')
@section('title')
{{("Save Doctors")}} | {{("Admin")}} {{("Doctors")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Save Doctors")}}</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ url('admin/doctors') }}" class="text-primary-500 hover:underline">{{("Doctors")}}</a>
                    / {{("Save Doctors")}}
                </div>
            </div>

            <div class="flex justify-center">
                <div class="w-full lg:w-3/4">
                    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                        <form action="{{url('admin/updatedoctor')}}" class="space-y-6" method="post" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <input type="hidden" name="id" id="doctor_id" value="{{$id}}">

                            <div class="mb-4">
                                <label for="upload_image" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Profile Image")}}</label>
                                <div class="flex items-center">
                                    <div class="mr-4">
                                        @php
                                        $imagePath = isset($data->image) ? asset('upload/doctors/' . $data->image) : asset('upload/profile/profile.png');
                                        @endphp
                                        <img src="{{ $imagePath }}" alt="Profile" class="rounded-full h-20 w-20 object-cover border border-gray-300 dark:border-gray-600" id="basic_img">
                                        <input type="hidden" name="real_basic_img" id="real_basic_img" value="{{ $data->image ?? '' }}"/>
                                    </div>
                                    <div>
                                        <input type="file" name="upload_image" id="upload_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Name")}} <span class="text-red-500">*</span></label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="name" name="name" placeholder='{{("Enter Doctor Name")}}' required value="{{ $data->name ?? '' }}">
                            </div>

                            <div>
                                <label for="department_id" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Specialities")}} <span class="text-red-500">*</span></label>
                                <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" name="department_id" id="department_id" required>
                                    <option value="">{{("Select Speciality")}}</option>
                                    @foreach($department as $d)
                                        <option value="{{$d->id}}" {{ (isset($data->department_id) && $data->department_id == $d->id) ? 'selected' : '' }}>{{$d->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="password" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                                    {{("Password")}}
                                    @if($id == 0) <span class="text-red-500">*</span> @endif
                                </label>
                                <input type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="password" name="password" 
                                       placeholder='{{ $id == 0 ? __("Enter Password") : __("Leave blank to keep current password") }}' {{ $id == 0 ? 'required' : '' }}>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="phoneno" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Phone")}} <span class="text-red-500">*</span></label>
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="phoneno" name="phoneno" placeholder='{{("Enter Phone")}}' required value="{{ $data->phoneno ?? '' }}">
                                </div>
                                <div>
                                    <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Email")}} <span class="text-red-500">*</span></label>
                                    <input type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="email" name="email" placeholder='{{("Enter Email Address")}}' required {{ isset($id) && $id != 0 ? 'readonly' : '' }} value="{{ $data->email ?? '' }}">
                                </div>
                                <div>
                                    <label for="working_time" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Working Time")}} <span class="text-red-500">*</span></label>
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="working_time" name="working_time" placeholder='{{("Enter Working Time")}}' required value="{{ $data->working_time ?? '' }}">
                                </div>
                                <div>
                                    <label for="consultation_fees" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Consultation Fees")}} <span class="text-red-500">*</span></label>
                                    <input type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="consultation_fees" name="consultation_fees" placeholder='{{("Enter Consultation Fees")}}' required value="{{ $data->consultation_fees ?? '' }}" min="1" step="0.01">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="facebook_url" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Facebook URL")}}</label>
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="facebook_url" name="facebook_url" placeholder='{{("Enter Facebook URL")}}' value="{{ $data->facebook_url ?? '' }}">
                                </div>
                                <div>
                                    <label for="twitter_url" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Twitter URL")}}</label>
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="twitter_url" name="twitter_url" placeholder='{{("Enter Twitter URL")}}' value="{{ $data->twitter_url ?? '' }}">
                                </div>
                            </div>

                            {{-- AI Profile Assistant Box --}}
                            <div class="bg-gradient-to-r from-primary-50 via-emerald-50 to-teal-50 dark:from-gray-700/60 dark:to-gray-700/30 p-4 rounded-xl border border-primary-200 dark:border-gray-600 shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary-600 text-white shadow-sm text-sm">
                                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                            </span>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">AI Profile Assistant & SEO Copywriter</h4>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                            Auto-generate professional, engaging, SEO-optimized biographies, clinical services, and care descriptions based on doctor's specialty.
                                        </p>
                                    </div>
                                    <button type="button" id="btn-ai-generate-doc" onclick="generateDoctorBioAI()" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-primary-600 to-emerald-600 hover:from-primary-700 hover:to-emerald-700 text-white text-xs font-bold rounded-lg shadow transition-all duration-200 whitespace-nowrap">
                                        <i class="fa-solid fa-sparkles mr-1.5" id="ai-btn-icon"></i>
                                        <span id="ai-btn-text">✨ Generate / Enhance Bio with AI</span>
                                    </button>
                                </div>
                                <div id="ai-gen-status" class="hidden mt-3 text-xs text-primary-700 dark:text-primary-300 flex items-center space-x-2">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                                    <span>AI is writing SEO medical biography and structured services...</span>
                                </div>
                            </div>

                            <div>
                                <label for="aboutus" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("About Us")}} <span class="text-red-500">*</span></label>
                                <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="aboutus" name="aboutus" placeholder='{{("Enter About Doctor")}}' rows="4" required>{{ $data->aboutus ?? '' }}</textarea>
                            </div>

                            <div>
                                <label for="services" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Services")}} <span class="text-red-500">*</span></label>
                                <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="services" name="services" placeholder='{{("Enter Description about Services")}}' rows="4" required>{{ $data->services ?? '' }}</textarea>
                            </div>

                            <div>
                                <label for="healthcare" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Health Care")}} <span class="text-red-500">*</span></label>
                                <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="healthcare" name="healthcare" placeholder='{{("Enter Health Care")}}' rows="4" required>{{ $data->healthcare ?? '' }}</textarea>
                            </div>

                            <div>
                                <label for="us2-address" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Address")}} <span class="text-red-500">*</span></label>
                                <input type="text" id="us2-address" name="address" placeholder='{{("Search Location")}}' required data-parsley-required="true" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" value="{{ $data->address ?? '' }}"/>
                            </div>

                            <div class="w-full h-64 rounded-md overflow-hidden" id="us2"></div>

                            <input type="hidden" name="lat" id="us2-lat" value="{{ $data->lat ?? Config::get('mapdetail.lat') }}" />
                            <input type="hidden" name="lon" id="us2-lon" value="{{ $data->lon ?? Config::get('mapdetail.long') }}" />

                            <div class="flex justify-end">
                                @if(Session::get("is_demo")=='0')
                                <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{('Submit')}}</button>
                                @else
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Submit")}}</button>
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
function generateDoctorBioAI() {
    const docName = document.getElementById('name')?.value || '';
    const deptSelect = document.getElementById('department_id');
    const departmentId = deptSelect?.value || '';
    const specialty = deptSelect?.options[deptSelect.selectedIndex]?.text || '';
    const address = document.getElementById('us2-address')?.value || '';
    const workingTime = document.getElementById('working_time')?.value || '';
    const currentAbout = document.getElementById('aboutus')?.value || '';
    const currentServices = document.getElementById('services')?.value || '';
    const currentHealthcare = document.getElementById('healthcare')?.value || '';

    if (!docName.trim()) {
        alert('Please enter the Doctor Name first so AI can personalize the profile.');
        document.getElementById('name')?.focus();
        return;
    }

    const btn = document.getElementById('btn-ai-generate-doc');
    const btnText = document.getElementById('ai-btn-text');
    const btnIcon = document.getElementById('ai-btn-icon');
    const statusDiv = document.getElementById('ai-gen-status');

    btn.disabled = true;
    btnText.innerText = 'Generating with AI...';
    btnIcon.className = 'fa-solid fa-spinner fa-spin mr-1.5';
    statusDiv.classList.remove('hidden');

    fetch("{{ route('admin.ai.doctor_bio') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            name: docName,
            department_id: departmentId,
            specialty: specialty,
            address: address,
            working_time: workingTime,
            existing_about: currentAbout,
            existing_services: currentServices,
            existing_healthcare: currentHealthcare,
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btnText.innerText = '✨ Re-Generate with AI';
        btnIcon.className = 'fa-solid fa-sparkles mr-1.5';
        statusDiv.classList.add('hidden');

        if (data.status === 'success' && data.data) {
            const result = data.data;
            if (result.aboutus) {
                const aboutEl = document.getElementById('aboutus');
                aboutEl.value = result.aboutus;
                aboutEl.classList.add('bg-green-50', 'dark:bg-green-900/20');
                setTimeout(() => aboutEl.classList.remove('bg-green-50', 'dark:bg-green-900/20'), 1500);
            }
            if (result.services) {
                const servEl = document.getElementById('services');
                servEl.value = result.services;
                servEl.classList.add('bg-green-50', 'dark:bg-green-900/20');
                setTimeout(() => servEl.classList.remove('bg-green-50', 'dark:bg-green-900/20'), 1500);
            }
            if (result.healthcare) {
                const healthEl = document.getElementById('healthcare');
                healthEl.value = result.healthcare;
                healthEl.classList.add('bg-green-50', 'dark:bg-green-900/20');
                setTimeout(() => healthEl.classList.remove('bg-green-50', 'dark:bg-green-900/20'), 1500);
            }
        } else {
            alert(data.message || 'Failed to generate profile with AI. Please try again.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btnText.innerText = '✨ Generate / Enhance Bio with AI';
        btnIcon.className = 'fa-solid fa-sparkles mr-1.5';
        statusDiv.classList.add('hidden');
        alert('Network error while communicating with AI service.');
        console.error(err);
    });
}
</script>
@stop
