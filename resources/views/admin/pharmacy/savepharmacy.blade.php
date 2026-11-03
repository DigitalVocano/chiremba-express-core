@extends('admin.layout')
@section('title')
{{("Save Pharmacy")}} | {{("Admin")}}
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Save Pharmacy")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{url('admin/pharmacy')}}" class="text-primary-500 hover:underline">{{("Pharmacy")}}</a>
               / {{("Save Pharmacy")}}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <form action="{{url('admin/updatepharmacy')}}" class="space-y-6" method="post" enctype="multipart/form-data">
                     {{csrf_field()}}
                     <input type="hidden" name="id" id="doctor_id" value="{{$id}}">

                     <div class="mb-4">
                        <label for="upload_image" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Image")}}</label>
                        <div class="flex items-center">
                           <div class="mr-4">
                              @php
                                 $imagePath = isset($data->image) ? asset('upload/doctors/' . $data->image) : asset('upload/doctors/medicine.png');
                              @endphp
                              <img src="{{ $imagePath }}" alt="Pharmacy Image" class="h-20 w-20 object-cover rounded-md border border-gray-300 dark:border-gray-600" id="basic_img">
                              <input type="hidden" name="real_basic_img" id="real_basic_img" value="{{ $data->image ?? '' }}"/>
                           </div>
                           <div>
                              <input type="file" name="upload_image" id="upload_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700">
                           </div>
                        </div>
                     </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                           <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Name")}}<span class="text-red-500">*</span></label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder='{{("Enter Pharmacy Name")}}' id="name" name="name" required value="{{ $data->name ?? '' }}">
                        </div>
                        <div>
                           <label for="password" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Password")}}<span class="text-red-500">*</span></label>
                           <input type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="password" placeholder='{{("Enter password")}}' name="password" required value="{{ $data->password ?? '' }}">
                        </div>
                     </div>

                     <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                           <label for="phoneno" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Phone")}}<span class="text-red-500">*</span></label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="phoneno" placeholder='{{("Enter Phone")}}' name="phoneno" required value="{{ $data->phoneno ?? '' }}">
                        </div>
                        <div>
                           <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Email")}}<span class="text-red-500">*</span></label>
                           <input type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="email" placeholder='{{("Enter Email Address")}}' name="email" required {{ isset($id) && $id != 0 ? 'readonly' : '' }} value="{{ $data->email ?? '' }}">
                        </div>
                        <div>
                           <label for="working_time" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Working Time")}}<span class="text-red-500">*</span></label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="working_time" placeholder='{{("Enter Working Time")}}' name="working_time" required value="{{ $data->working_time ?? '' }}">
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
                                 <h4 class="text-sm font-bold text-gray-900 dark:text-white">AI Pharmacy Assistant & SEO Copywriter</h4>
                              </div>
                              <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                 Auto-generate professional, engaging, SEO-optimized pharmacy biographies and structured pharmaceutical services.
                              </p>
                           </div>
                           <button type="button" id="btn-ai-generate-pharm" onclick="generatePharmacyBioAI()" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-primary-600 to-emerald-600 hover:from-primary-700 hover:to-emerald-700 text-white text-xs font-bold rounded-lg shadow transition-all duration-200 whitespace-nowrap">
                              <i class="fa-solid fa-sparkles mr-1.5" id="ai-pharm-icon"></i>
                              <span id="ai-pharm-text">✨ Generate / Enhance with AI</span>
                           </button>
                        </div>
                        <div id="ai-pharm-status" class="hidden mt-3 text-xs text-primary-700 dark:text-primary-300 flex items-center space-x-2">
                           <i class="fa-solid fa-circle-notch fa-spin"></i>
                           <span>AI is writing SEO pharmacy description and clinical dispensing services...</span>
                        </div>
                     </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                           <label for="aboutus" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("About Us")}}<span class="text-red-500">*</span></label>
                           <textarea id="aboutus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="5" name="aboutus" placeholder='{{("Enter About Pharmacy")}}' required>{{ $data->aboutus ?? '' }}</textarea>
                        </div>
                        <div>
                           <label for="services" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Services")}}<span class="text-red-500">*</span></label>
                           <textarea id="services" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="5" placeholder='{{("Enter Description about Services")}}' name="services" required>{{ $data->services ?? '' }}</textarea>
                        </div>
                     </div>

                     <div>
                        <label for="us2-address" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">{{("Address")}}<span class="text-red-500">*</span></label>
                        <input type="text" id="us2-address" name="address" placeholder='{{("Search Location")}}' required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" value="{{ $data->address ?? '' }}"/>
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
function generatePharmacyBioAI() {
    const pharmName = document.getElementById('name')?.value || '';
    const address = document.getElementById('us2-address')?.value || '';
    const workingTime = document.getElementById('working_time')?.value || '';
    const currentAbout = document.getElementById('aboutus')?.value || '';
    const currentServices = document.getElementById('services')?.value || '';

    if (!pharmName.trim()) {
        alert('Please enter the Pharmacy Name first so AI can personalize the profile.');
        document.getElementById('name')?.focus();
        return;
    }

    const btn = document.getElementById('btn-ai-generate-pharm');
    const btnText = document.getElementById('ai-pharm-text');
    const btnIcon = document.getElementById('ai-pharm-icon');
    const statusDiv = document.getElementById('ai-pharm-status');

    btn.disabled = true;
    btnText.innerText = 'Generating with AI...';
    btnIcon.className = 'fa-solid fa-spinner fa-spin mr-1.5';
    statusDiv.classList.remove('hidden');

    fetch("{{ route('admin.ai.pharmacy_bio') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            name: pharmName,
            address: address,
            working_time: workingTime,
            existing_about: currentAbout,
            existing_services: currentServices,
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
        } else {
            alert(data.message || 'Failed to generate pharmacy profile with AI. Please try again.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btnText.innerText = '✨ Generate / Enhance with AI';
        btnIcon.className = 'fa-solid fa-sparkles mr-1.5';
        statusDiv.classList.add('hidden');
        alert('Network error while communicating with AI service.');
        console.error(err);
    });
}
</script>
@stop
