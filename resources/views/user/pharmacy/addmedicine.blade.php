@extends('layouts.pharmacy')
@section('title')
    Medicine
@stop
@section('meta-data')
    <meta property="og:type" content="website" />
    <meta property="og:url" content="KumbaCare" />
    <meta property="og:title" content="KumbaCare" />
    <meta property="og:image" content="{{ asset('image_web/') . '/' . $setting->favicon }}" />
    <meta property="og:image:width" content="250px" /> {{-- Standard size for social media --}}
    <meta property="og:image:height" content="250px" /> {{-- Standard size for social media --}}
    <meta property="og:site_name" content="KumbaCare" />
    <meta property="og:description" content="KumbaCare is a telemedication web app." />
    <meta property="og:keyword" content="telemedication, doctor, patient, pharmacy" />
    <link rel="shortcut icon" href="{{ asset('image_web/') . '/' . $setting->favicon }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
<div x-data="medicineManager()">
    {{-- Page Header with Search and Add Button --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Manage Medicines')}}</h1>
        <div class="flex items-center space-x-4 w-full sm:w-auto">
            <div class="relative flex-grow sm:flex-grow-0">
                <input type="text" x-model.debounce.300ms="search" placeholder="{{__('Search medicines...')}}" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:border-gray-600">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
            <button @click="openModal()" type="button" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 flex items-center shrink-0">
                <i class="fas fa-plus mr-2"></i>{{__('Add Medicine')}}
            </button>
        </div>
    </div>

    {{-- Medicine Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <template x-for="medicine in filteredMedicines" :key="medicine.id">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                <img :src="`{{ asset('upload/pharmacymedicine') }}/${medicine.image}`" :alt="medicine.name" class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 truncate" x-text="medicine.name"></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="`{{ $setting->currency }}${parseFloat(medicine.price).toFixed(2)}`"></p>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 h-16 overflow-hidden" x-text="limitDescription(medicine.description)"></p>
                    <div class="mt-4 flex justify-end space-x-3">
                        <button @click="editMedicine(medicine)" class="text-blue-500 hover:text-blue-700 dark:hover:text-blue-400" title="Edit">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button @click="confirmDelete(medicine.id)" class="text-red-500 hover:text-red-700 dark:hover:text-red-400" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        
        <template x-if="filteredMedicines.length === 0">
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <p class="text-gray-500 dark:text-gray-400">No Data Found</p>
            </div>
        </template>
    </div>

    {{-- Add/Edit Medicine Modal --}}
    <div x-show="showMedicineModal" style="display: none;" @keydown.escape.window="closeModal()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="medicineModalTitle" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div x-show="showMedicineModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="closeModal(); showMasterSuggestions = false" aria-hidden="true"></div>
            
            {{-- Modal Panel --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showMedicineModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="medicineModalTitle" x-text="isEditing ? 'Edit Medicine' : 'Add Medicine'"></h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                        <span class="sr-only">Close</span> <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mt-4">
                    <form action="{{ url('updatemedicinefront') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="pharmacy_id" value="{{ Session::get('user_id') }}">
                        <input type="hidden" name="medicine_id" :value="medicine.id || 0">

                        <div class="space-y-4">
                            <div>
                                <label for="upload_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Image</label>
                                <div class="mt-1 flex items-center space-x-4">
                                    <img :src="imagePreview" alt="Image Preview" class="w-24 h-24 object-cover rounded-md bg-gray-100 dark:bg-gray-700">
                                    <input type="file" name="upload_image" id="upload_image" @change="showPreview" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700"/>
                                </div>
                            </div>

                            <div class="relative">
                                <label for="master_search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Master Database (Optional)</label>
                                <input type="text" id="master_search" @input.debounce.300ms="fetchMasterMedicines($event.target.value)" placeholder="Search to auto-fill..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:border-gray-600">
                                <div x-show="showMasterSuggestions" class="absolute z-10 w-full bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-md mt-1 max-h-48 overflow-y-auto shadow-lg">
                                    <template x-for="suggestion in masterSuggestions" :key="suggestion.id">
                                        <div @click="selectMasterMedicine(suggestion)" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" x-text="suggestion.name"></div>
                                    </template>
                                    <template x-if="masterSuggestions.length === 0">
                                        <div class="px-4 py-2 text-gray-500">No results found</div>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name<span class="text-red-500">*</span></label>
                                    <input type="text" x-model="medicine.name" name="name" id="name" placeholder='Enter Medicine Name' required class="mt-1 block w-full form-input">
                                </div>
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category<span class="text-red-500">*</span></label>
                                    <select name="category_id" id="category_id" x-model="medicine.category_id" required class="mt-1 block w-full form-select">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selling Price<span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" x-model="medicine.price" name="price" id="price" placeholder='0.00' required class="mt-1 block w-full form-input">
                                </div>
                                <div>
                                    <label for="cost_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Price<span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" x-model="medicine.cost_price" name="cost_price" id="cost_price" placeholder='0.00' required class="mt-1 block w-full form-input">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Quantity<span class="text-red-500">*</span></label>
                                    <input type="number" x-model="medicine.stock" name="stock" id="stock" placeholder='0' required class="mt-1 block w-full form-input">
                                </div>
                                <div class="flex items-center mt-6">
                                    <input type="hidden" name="is_prescription_required" :value="medicine.is_prescription_required ? '1' : '0'">
                                    <input type="checkbox" x-model="medicine.is_prescription_required" id="is_prescription_required" class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <label for="is_prescription_required" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Requires Prescription?</label>
                                </div>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea x-model="medicine.description" id="description" name="description" rows="3" placeholder='Enter Description' class="mt-1 block w-full form-textarea"></textarea>
                            </div>

                            <div>
                                <label for="dosage_rules" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dosage Rules (Optional)</label>
                                <textarea x-model="medicine.dosage_rules" id="dosage_rules" name="dosage_rules" rows="2" placeholder='e.g., One tablet twice a day' class="mt-1 block w-full form-textarea"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 focus:outline-none">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteConfirmModal" style="display: none;" @keydown.escape.window="showDeleteConfirmModal = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="deleteModalTitle" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div x-show="showDeleteConfirmModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="showDeleteConfirmModal = false" aria-hidden="true"></div>
            
            {{-- Modal Panel --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showDeleteConfirmModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-sm p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="deleteModalTitle">Confirm Deletion</h3>
                    <button type="button" @click="showDeleteConfirmModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                        <span class="sr-only">Close</span> <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Are you sure you want to delete this medicine? This action cannot be undone.</p>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showDeleteConfirmModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 focus:outline-none">
                        Cancel
                    </button>
                    <button type="button" @click="executeDelete()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('footer_scripts')
<script>
    function medicineManager() {
        const defaultImageUrl = "{{ asset('upload/doctors/defaultpharmacy.png') }}";
        const assetBaseUrl = "{{ asset('upload/pharmacymedicine') }}";
        const allMedicines = @json($data);

        return {
            showMedicineModal: false,
            showDeleteConfirmModal: false,
            isEditing: false,
            medicine: {},
            imagePreview: defaultImageUrl,
            search: '',
            masterSearch: '',
            masterSuggestions: [],
            showMasterSuggestions: false,
            medicines: allMedicines,
            medicineToDeleteId: null,

            get filteredMedicines() {
                if (this.search.trim() === '') {
                    return this.medicines;
                }
                return this.medicines.filter(med => {
                    const searchLower = this.search.toLowerCase();
                    const nameMatch = med.name && med.name.toLowerCase().includes(searchLower);
                    const descriptionMatch = med.description && med.description.toLowerCase().includes(searchLower);
                    return nameMatch || descriptionMatch;
                });
            },
            
            init() {
                this.resetForm();
            },

            resetForm() {
                this.medicine = {
                    id: null,
                    name: '',
                    price: '',
                    cost_price: '',
                    stock: '',
                    category_id: '',
                    is_prescription_required: false,
                    dosage_rules: '',
                    description: '',
                };
                this.imagePreview = defaultImageUrl;
                this.isEditing = false;
            },

            openModal() {
                this.resetForm();
                this.showMedicineModal = true;
            },

            closeModal() {
                this.showMedicineModal = false;
            },

            editMedicine(med) {
                this.medicine = { ...med };
                this.imagePreview = med.image ? `${assetBaseUrl}/${med.image}` : defaultImageUrl;
                // Ensure boolean conversion for checkbox
                this.medicine.is_prescription_required = (med.is_prescription_required == 1 || med.is_prescription_required === true);
                this.isEditing = true;
                this.showMedicineModal = true;
            },

            confirmDelete(id) {
                this.medicineToDeleteId = id;
                this.showDeleteConfirmModal = true;
            },

            showPreview(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            deleteUrl(id) {
                return `{{ url('medicinedeletefront') }}/${id}`;
            },

            executeDelete() {
                window.location.href = this.deleteUrl(this.medicineToDeleteId);
            },

            fetchMasterMedicines(query) {
                if (query.length < 2) {
                    this.masterSuggestions = [];
                    this.showMasterSuggestions = false;
                    return;
                }
                fetch(`{{ url('/api/search_master_medicine') }}?term=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success === "1") {
                            this.masterSuggestions = data.data;
                            this.showMasterSuggestions = true;
                        }
                    });
            },

            selectMasterMedicine(suggestion) {
                this.medicine.name = suggestion.name;
                this.medicine.description = suggestion.description || '';
                this.medicine.dosage_rules = suggestion.dosage_rules || '';
                // Note: price, stock, etc., are left for the pharmacy to set.
                this.showMasterSuggestions = false;
            },

            limitDescription(description) {
                if (!description) return '';
                const limit = 100;
                return description.length > limit ? description.substring(0, limit) + '...' : description;
            }
        };
    }
</script>
@stop
