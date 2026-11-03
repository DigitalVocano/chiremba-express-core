@extends('layouts.doctor')
@section('title')
{{__("My Profile")}}
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
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__("My Profile")}}</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-4xl mx-auto">
    @if(Session::has('message'))
                <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-blue-700 bg-blue-100 dark:bg-blue-200 dark:text-blue-800') }} rounded-lg" role="alert">
                    {{ Session::get('message') }}
                </div>
                @endif
                <form action="{{url('updatedoctor')}}" method="post" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="flex flex-col items-center mb-6">
                        <figure class="relative w-32 h-32 rounded-full overflow-hidden border-2 border-gray-300 dark:border-gray-600">
                            @if($doctordata->image!="")
                            <img id="profileImagePreview" src="{{asset('upload/doctors').'/'.$doctordata->image}}" alt="Doctor Profile" class="object-cover w-full h-full">
                            @else
                            <img id="profileImagePreview" src="{{asset('upload/doctors/defaultpharmacy.png')}}" alt="Default Profile" class="object-cover w-full h-full">
                            @endif
                        </figure>
                        <div class="mt-4">
                            <label for="upload_image" class="cursor-pointer bg-primary-600 hover:bg-primary-700 text-white text-sm py-2 px-4 rounded-md">
                                {{__('Upload Photo')}}
                            </label>
                            <input type="file" name="upload_image" id="upload_image" class="hidden" accept="image/*" onchange="previewImage(event)">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Name')}}</label>
                            <input type="text" name="name" id="name" placeholder="{{__('Enter Doctor Name')}}" required value="{{isset($doctordata->name)?$doctordata->name:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Specialist')}}</label>
                            <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                <option value="">{{__('Select Specialist')}}</option>
                                @foreach($department as $dp)
                                <option value="{{$dp->id}}" <?= isset($doctordata->department_id)&&$dp->id==$doctordata->department_id?'selected="selected"':""?> >{{$dp->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Email')}}</label>
                            <input type="email" name="email" placeholder="{{__('Your email')}}" required id="email" value="{{isset($doctordata->email)?$doctordata->email:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="phoneno" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Phone no')}}</label>
                            <input type="text" name="phoneno" id="phoneno" placeholder="{{__('Enter Phone No')}}" required value="{{isset($doctordata->phoneno)?$doctordata->phoneno:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="consultation_fees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__("consultation_fees")}}<span class="text-red-500">*</span></label>
                            <input type="number" required name="consultation_fees" value="{{isset($doctordata->consultation_fees)?$doctordata->consultation_fees:''}}" id="consultation_fees" min="1" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="working_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Working Time')}}</label>
                            <input type="text" name="working_time" placeholder="{{__('Enter Working Time')}}" required id="working_time" value="{{isset($doctordata->working_time)?$doctordata->working_time:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="facebook_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Facebook URL')}}</label>
                            <input type="url" name="facebook_url" id="facebook_url" placeholder="https://facebook.com/yourprofile" value="{{isset($doctordata->facebook_url)?$doctordata->facebook_url:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="twitter_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Twitter URL')}}</label>
                            <input type="url" name="twitter_url" id="twitter_url" placeholder="https://twitter.com/yourprofile" value="{{isset($doctordata->twitter_url)?$doctordata->twitter_url:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>

                        <!-- Direct P2P Payment Setup Section -->
                        <div class="md:col-span-2 border-t pt-4 mt-2">
                            <h3 class="text-md font-semibold text-gray-800 dark:text-gray-100 mb-2">{{__('Direct P2P Payment Details (EcoCash & Bank)')}}</h3>
                            <p class="text-xs text-gray-500 mb-4">{{__('These payment details are displayed to patients when Direct Provider Payment Mode is enabled by Admin.')}}</p>
                        </div>
                        <div class="form-group">
                            <label for="ecocash_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('EcoCash Number')}}</label>
                            <input type="text" name="ecocash_number" id="ecocash_number" placeholder="077XXXXXXX" value="{{isset($doctordata->ecocash_number)?$doctordata->ecocash_number:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="ecocash_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('EcoCash Account Name')}}</label>
                            <input type="text" name="ecocash_name" id="ecocash_name" placeholder="Name on EcoCash account" value="{{isset($doctordata->ecocash_name)?$doctordata->ecocash_name:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Bank Name')}}</label>
                            <input type="text" name="bank_name" id="bank_name" placeholder="e.g. CABS, Stanbic, FBC" value="{{isset($doctordata->bank_name)?$doctordata->bank_name:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="bank_account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Bank Account Number')}}</label>
                            <input type="text" name="bank_account_number" id="bank_account_number" placeholder="Account Number" value="{{isset($doctordata->bank_account_number)?$doctordata->bank_account_number:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="bank_account_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Bank Account Holder Name')}}</label>
                            <input type="text" name="bank_account_name" id="bank_account_name" placeholder="Account Holder Name" value="{{isset($doctordata->bank_account_name)?$doctordata->bank_account_name:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <div class="form-group">
                            <label for="innbucks_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('InnBucks Number')}}</label>
                            <input type="text" name="innbucks_number" id="innbucks_number" placeholder="07XXXXXXXX" value="{{isset($doctordata->innbucks_number)?$doctordata->innbucks_number:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <!-- Accepted Medical Aid Societies Section -->
                        <div class="md:col-span-2 border-t pt-4 mt-2">
                            <h3 class="text-md font-semibold text-gray-800 dark:text-gray-100 mb-2">{{__('Accepted Medical Aid Societies (Zimbabwe)')}}</h3>
                            <p class="text-xs text-gray-500 mb-4">{{__('Select all Medical Aid societies accepted at your practice for appointment consultations.')}}</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @php
                                    $allSocieties = \App\Models\MedicalAidSociety::where('is_active', 1)->orderBy('name', 'ASC')->get();
                                    $acceptedIds = isset($doctordata) ? $doctordata->medicalAids()->pluck('medical_aid_societies.id')->toArray() : [];
                                @endphp
                                @foreach($allSocieties as $soc)
                                    <label class="inline-flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="medical_aids[]" value="{{ $soc->id }}" {{ in_array($soc->id, $acceptedIds) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <span>{{ $soc->name }} ({{ $soc->code }})</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group md:col-span-2">
                            <label for="aboutus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('About Us')}}</label>
                            <textarea name="aboutus" id="aboutus" placeholder="{{__('Enter About Doctor')}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 rows-3">{{isset($doctordata->aboutus)?$doctordata->aboutus:''}}</textarea>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label for="services" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Services')}}</label>
                            <textarea name="services" id="services" placeholder="{{__('Enter Description about Services')}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 rows-3">{{isset($doctordata->services)?$doctordata->services:''}}</textarea>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label for="healthcare" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Health Care')}}</label>
                            <textarea name="healthcare" id="healthcare" placeholder="{{__('Enter Health Care')}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 rows-3">{{isset($doctordata->healthcare)?$doctordata->healthcare:''}}</textarea>
                        </div>
                        <div class="form-group md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label for="us2-address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__("Address")}}<span class="text-red-500">*</span></label>
                                <button type="button" onclick="detectCurrentDoctorLocation()" class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-md shadow-sm transition">
                                    <i class="fas fa-crosshairs mr-1.5"></i> {{__("Use Current GPS Location")}}
                                </button>
                            </div>
                            <input type="text" id="us2-address" name="address" placeholder='{{__("Search Location or use GPS button above")}}' required value="{{isset($doctordata->address)?$doctordata->address:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
                        </div>
                        <div class="form-group md:col-span-2">
                            <div id="us2" style="width: 100%; height: 300px;"></div>
                        </div>
                        <input type="hidden" name="lat" id="us2-lat" value="{{isset($doctordata->lat)?$doctordata->lat:Config::get('mapdetail.lat')}}" />
                        <input type="hidden" name="lon" id="us2-lon" value="{{isset($doctordata->lon)?$doctordata->lon:Config::get('mapdetail.long')}}" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{url('doctoreditprofile')}}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 dark:border-gray-500">{{__('Cancel')}}</a>
                        <button class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600" type="submit">
                            {{__('Save Changes')}}
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            {{-- </div> --}}
        {{-- </div> --}}
    {{-- </div> --}}
</div>
@endsection

@section('footer_scripts')
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('profileImagePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Initialize Location Picker
    $('#us2').locationpicker({
        location: {latitude: $('#us2-lat').val() || {{ Config::get('mapdetail.lat', 0) }}, longitude: $('#us2-lon').val() || {{ Config::get('mapdetail.long', 0) }}},
        radius: 0,
        inputBinding: {
            latitudeInput: $('#us2-lat'),
            longitudeInput: $('#us2-lon'),
            locationNameInput: $('#us2-address')
        },
        enableAutocomplete: true
    });

    $('#us2-address').on('change blur', function() {
        var addr = $(this).val();
        if (addr && typeof google !== 'undefined' && google.maps) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: addr }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    var lat = results[0].geometry.location.lat();
                    var lng = results[0].geometry.location.lng();
                    $('#us2-lat').val(lat);
                    $('#us2-lon').val(lng);
                    $('#us2').locationpicker('location', {
                        latitude: lat,
                        longitude: lng
                    });
                }
            });
        }
    });

    function detectCurrentDoctorLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            $('#us2-lat').val(lat);
            $('#us2-lon').val(lng);
            $('#us2').locationpicker('location', {
                latitude: lat,
                longitude: lng
            });
            if (typeof google !== 'undefined' && google.maps) {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        $('#us2-address').val(results[0].formatted_address);
                    }
                });
            }
        }, function(error) {
            alert('Unable to retrieve GPS location: ' + error.message);
        }, { enableHighAccuracy: true });
    }
</script>
@endsection
