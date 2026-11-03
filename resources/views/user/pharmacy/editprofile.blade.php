@extends('layouts.pharmacy')
@section('title')
My Profile
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
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">My Profile</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    @if(Session::has('message'))
    <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-blue-700 bg-blue-100 dark:bg-blue-200 dark:text-blue-800') }} rounded-lg" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif

    <form action="{{url('updatepharmacy')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="flex flex-col items-center mb-6">
            <figure class="relative w-32 h-32 rounded-full overflow-hidden border-2 border-gray-300 dark:border-gray-600">
                @if($doctordata->image!="")
                <img id="profileImagePreview" src="{{asset('upload/doctors').'/'.$doctordata->image}}" alt="Pharmacy Profile" class="object-cover w-full h-full">
                @else
                <img id="profileImagePreview" src="{{asset('upload/doctors/defaultpharmacy.png')}}" alt="Default Profile" class="object-cover w-full h-full">
                @endif
            </figure>
            <div class="mt-4">
                <label for="upload_image" class="cursor-pointer bg-primary-600 hover:bg-primary-700 text-white text-sm py-2 px-4 rounded-md">
                    Upload Photo
                </label>
                <input type="file" name="upload_image" id="upload_image" class="hidden" accept="image/*" onchange="previewImage(event)">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <input type="text" name="name" id="name" placeholder="Enter Pharmacy Name" required value="{{isset($doctordata->name)?$doctordata->name:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
            </div>

            <div class="form-group">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" placeholder="Your email" required id="email" value="{{isset($doctordata->email)?$doctordata->email:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
            </div>
            <div class="form-group">
                <label for="phoneno" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone No</label>
                <input type="text" name="phoneno" id="phoneno" placeholder="Enter Phone No" required value="{{isset($doctordata->phoneno)?$doctordata->phoneno:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
            </div>

            <div class="form-group">
                <label for="working_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Working Time</label>
                <input type="text" name="working_time" placeholder="e.g., 9:00 AM - 5:00 PM" required id="working_time" value="{{isset($doctordata->working_time)?$doctordata->working_time:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
            </div>

            <!-- Payment Methods & Delivery Settings -->
            <div class="form-group md:col-span-2 p-4 border rounded-md dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-3">Accepted Payment Methods & Delivery Mode</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="accept_cod" value="1" {{ ($doctordata->accept_cod ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Accept Cash on Delivery (COD)</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="accept_digital_payments" value="1" {{ ($doctordata->accept_digital_payments ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Accept Digital Payments (Paynow / EcoCash / Wallet)</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="has_own_bikes" value="1" {{ ($doctordata->has_own_bikes ?? 0) == 1 ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Has Own Delivery Bikes (Pharmacy Fleet)</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="accept_pointlink_deliveries" value="1" {{ ($doctordata->accept_pointlink_deliveries ?? 1) == 1 ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Accept PointLink Network Couriers</span>
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="delivery_mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Fulfillment Mode</label>
                        <select name="delivery_mode" id="delivery_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                            <option value="pointlink" {{ ($doctordata->delivery_mode ?? 'pointlink') == 'pointlink' ? 'selected' : '' }}>PointLink Dispatch (Network Courier)</option>
                            <option value="self_delivery" {{ ($doctordata->delivery_mode ?? '') == 'self_delivery' ? 'selected' : '' }}>Self Delivery (Own Pharmacy Drivers)</option>
                            <option value="flat_rate" {{ ($doctordata->delivery_mode ?? '') == 'flat_rate' ? 'selected' : '' }}>Flat Rate Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label for="self_delivery_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Self Delivery Fee ($)</label>
                        <input type="number" step="0.5" name="self_delivery_fee" id="self_delivery_fee" value="{{ $doctordata->self_delivery_fee ?? 0 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                </div>
            </div>

            <!-- Direct P2P Payment Setup Section -->
            <div class="form-group md:col-span-2 p-4 border rounded-md dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-1">Direct P2P Payment Details (EcoCash & Bank Transfer)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">These details are shown to customers at checkout when Direct Provider Payment Mode is active.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ecocash_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">EcoCash Number</label>
                        <input type="text" name="ecocash_number" id="ecocash_number" placeholder="077XXXXXXX" value="{{ $doctordata->ecocash_number ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                    <div>
                        <label for="ecocash_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">EcoCash Account Name</label>
                        <input type="text" name="ecocash_name" id="ecocash_name" placeholder="Name on EcoCash account" value="{{ $doctordata->ecocash_name ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Name</label>
                        <input type="text" name="bank_name" id="bank_name" placeholder="e.g. CABS, Stanbic, FBC" value="{{ $doctordata->bank_name ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                    <div>
                        <label for="bank_account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Account Number</label>
                        <input type="text" name="bank_account_number" id="bank_account_number" placeholder="Account Number" value="{{ $doctordata->bank_account_number ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                    <div>
                        <label for="bank_account_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Account Holder Name</label>
                        <input type="text" name="bank_account_name" id="bank_account_name" placeholder="Holder Name" value="{{ $doctordata->bank_account_name ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                    <div>
                        <label for="innbucks_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">InnBucks Number</label>
                        <input type="text" name="innbucks_number" id="innbucks_number" placeholder="07XXXXXXXX" value="{{ $doctordata->innbucks_number ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    </div>
                </div>
            </div>

            <div class="form-group md:col-span-2">
                <label for="aboutus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">About Us</label>
                <textarea name="aboutus" id="aboutus" placeholder="Enter About Pharmacy" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 rows-3">{{isset($doctordata->aboutus)?$doctordata->aboutus:''}}</textarea>
            </div>
            <div class="form-group md:col-span-2">
                <label for="services" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Services</label>
                <textarea name="services"  id="services" placeholder="Enter Description about Services" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 rows-3">{{isset($doctordata->services)?$doctordata->services:''}}</textarea>
            </div>
            <div class="form-group md:col-span-2">
                <div class="flex items-center justify-between mb-1">
                    <label for="us2-address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address<span class="text-red-500">*</span></label>
                    <button type="button" onclick="detectCurrentPharmacyLocation()" class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-md shadow-sm transition">
                        <i class="fas fa-crosshairs mr-1.5"></i> Use Current GPS Location
                    </button>
                </div>
                <input type="text" id="us2-address" name="address" placeholder='Search Location or use GPS button above' required data-parsley-required="true" value="{{isset($doctordata->address)?$doctordata->address:''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
            </div>
            <div class="form-group md:col-span-2">
                <div id="us2" style="width: 100%; height: 300px;" class="rounded-md"></div>
            </div>
            <input type="hidden" name="lat" id="us2-lat" value="{{isset($doctordata->lat)?$doctordata->lat:Config::get('mapdetail.lat')}}" />
            <input type="hidden" name="lon" id="us2-lon" value="{{isset($doctordata->lon)?$doctordata->lon:Config::get('mapdetail.long')}}" />
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{url('pharmacyeditprofile')}}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 dark:border-gray-500">Cancel</a>
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
    var pharmacyMap, pharmacyMarker;

    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('profileImagePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // This function needs to be available globally or loaded with the Google Maps script
    function initializeMap() {
        if (document.getElementById('us2-lat') && document.getElementById('us2-lon') && typeof google !== 'undefined') {
            var lat = parseFloat(document.getElementById('us2-lat').value) || {{ Config::get('mapdetail.lat', 0) }};
            var lon = parseFloat(document.getElementById('us2-lon').value) || {{ Config::get('mapdetail.long', 0) }};
            pharmacyMap = new google.maps.Map(document.getElementById('us2'), {
                center: {lat: lat, lng: lon},
                zoom: 13
            });
            pharmacyMarker = new google.maps.Marker({
                position: {lat: lat, lng: lon},
                map: pharmacyMap,
                draggable: true
            });

            var searchBox = new google.maps.places.SearchBox(document.getElementById('us2-address'));

            pharmacyMap.addListener('bounds_changed', function() {
                searchBox.setBounds(pharmacyMap.getBounds());
            });

            searchBox.addListener('places_changed', function() {
                var places = searchBox.getPlaces();
                if (places.length == 0) {
                    return;
                }
                var place = places[0];
                pharmacyMarker.setPosition(place.geometry.location);
                pharmacyMap.setCenter(place.geometry.location);
                document.getElementById('us2-lat').value = place.geometry.location.lat();
                document.getElementById('us2-lon').value = place.geometry.location.lng();
            });

            google.maps.event.addListener(pharmacyMarker, 'dragend', function() {
                document.getElementById('us2-lat').value = pharmacyMarker.getPosition().lat();
                document.getElementById('us2-lon').value = pharmacyMarker.getPosition().lng();
                reverseGeocodeLatLng(pharmacyMarker.getPosition().lat(), pharmacyMarker.getPosition().lng());
            });

            document.getElementById('us2-address').addEventListener('change', function() {
                var addr = this.value;
                if (addr && typeof google !== 'undefined' && google.maps) {
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ address: addr }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            var loc = results[0].geometry.location;
                            document.getElementById('us2-lat').value = loc.lat();
                            document.getElementById('us2-lon').value = loc.lng();
                            pharmacyMarker.setPosition(loc);
                            pharmacyMap.setCenter(loc);
                            pharmacyMap.setZoom(16);
                        }
                    });
                }
            });
        }
    }

    function detectCurrentPharmacyLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            document.getElementById('us2-lat').value = lat;
            document.getElementById('us2-lon').value = lng;
            if (pharmacyMap && pharmacyMarker) {
                var pos = new google.maps.LatLng(lat, lng);
                pharmacyMarker.setPosition(pos);
                pharmacyMap.setCenter(pos);
                pharmacyMap.setZoom(16);
            }
            reverseGeocodeLatLng(lat, lng);
        }, function(error) {
            alert('Unable to retrieve current location: ' + error.message);
        }, { enableHighAccuracy: true });
    }

    function reverseGeocodeLatLng(lat, lng) {
        if (typeof google !== 'undefined' && google.maps) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    document.getElementById('us2-address').value = results[0].formatted_address;
                }
            });
        }
    }

    // Since the Google Maps script is loaded in the layout, we can call this directly.
    // It's wrapped to ensure it runs after the page content is loaded.
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof google !== 'undefined' && google.maps) {
            initializeMap();
        } else {
            // Fallback if the API script hasn't loaded for some reason.
            console.warn("Google Maps API not loaded. Map functionality will be limited.");
        }
    });
</script>
@endsection
