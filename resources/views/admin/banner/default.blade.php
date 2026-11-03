@extends('admin.layout')
@section('title')
{{("banner")}} | {{("Admin")}} {{("banner")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("banner")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               {{("banner")}}
            </div>
         </div>

         <div x-data="{ addModal: false, editModal: false, editId: null, editImage: '', editLinkUrl: '', editButtonText: '' }">
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
               @if(Session::has('message'))
               <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                  <span>{{ Session::get('message') }}</span>
                  <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
               </div>
               @endif

               <div class="flex justify-between items-center mb-4">
                  <h4 class="text-xl font-semibold text-gray-800 dark:text-white">{{("banner")}} {{("List")}}</h4>
                  <button @click="addModal = true" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Add Banner")}}</button>
               </div>

               <div class="overflow-x-auto">
                  <table id="bannertable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                     <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Image")}}</th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Action")}}</th>
                        </tr>
                     </thead>
                     <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- DataTables will populate this tbody --}}
                     </tbody>
                  </table>
               </div>
            </div>

            <!-- Add Banner Modal -->
            <div x-show="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-cloak>
               <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                  <div class="flex justify-between items-center pb-3">
                     <h5 class="text-xl font-semibold text-gray-900 dark:text-white">{{("Add Banner")}}</h5>
                     <button @click="addModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold">&times;</button>
                  </div>
                  <form action="{{url('admin/savebanner')}}" method="post" enctype="multipart/form-data" class="space-y-4">
                     {{csrf_field()}}
                     <div>
                        <label for="upload_file_add" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Upload Image(s)")}}</label>
                        <input type="file" id="upload_file_add" name="image[]" multiple required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700">
                     </div>
                     <div>
                        <label for="link_url_add" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Target URL / Ad Link (Optional)")}}</label>
                        <input type="url" id="link_url_add" name="link_url" placeholder="https://example.com/offer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                     </div>
                     <div>
                        <label for="button_text_add" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Button Label (e.g., Learn More, Install Now, Visit Website)")}}</label>
                        <input type="text" id="button_text_add" name="button_text" placeholder="Learn More" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                     </div>
                     <div class="flex justify-end space-x-2 pt-4">
                        <button type="button" @click="addModal = false" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">{{('Close')}}</button>
                        @if(Session::get("is_demo")=='0')
                           <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{('Submit')}}</button>
                        @else
                           <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Submit")}}</button>
                        @endif
                     </div>
                  </form>
               </div>
            </div>

            <!-- Edit Banner Modal -->
            <div x-show="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-cloak>
               <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                  <div class="flex justify-between items-center pb-3">
                     <h5 class="text-xl font-semibold text-gray-900 dark:text-white">{{("Edit Banner")}}</h5>
                     <button @click="editModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold">&times;</button>
                  </div>
                  <form action="{{url('admin/updatebanner')}}" method="post" enctype="multipart/form-data" class="space-y-4">
                     {{csrf_field()}}
                     <input type="hidden" name="id" x-model="editId">
                     <div>
                        <label for="upload_file_edit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Upload Image")}}</label>
                        <img :src="editImage" class="my-2 w-full h-auto max-h-48 object-contain rounded-md" alt="Current Banner">
                        <input type="file" id="upload_file_edit" name="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700">
                     </div>
                     <div>
                        <label for="link_url_edit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Target URL / Ad Link (Optional)")}}</label>
                        <input type="url" id="link_url_edit" name="link_url" x-model="editLinkUrl" placeholder="https://example.com/offer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                     </div>
                     <div>
                        <label for="button_text_edit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Button Label (e.g., Learn More, Install Now, Visit Website)")}}</label>
                        <input type="text" id="button_text_edit" name="button_text" x-model="editButtonText" placeholder="Learn More" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                     </div>
                     <div class="flex justify-end space-x-2 pt-4">
                        <button type="button" @click="editModal = false" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">{{('Close')}}</button>
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
function edit_img(id) {
    $.ajax({
        url: "{{url('admin/edit-img')}}/" + id,
        success: function(data) {
            let modal = document.querySelector('[x-data]');
            let alpineData = Alpine.$data(modal);
            alpineData.editId = id;
            if (typeof data === 'object') {
                alpineData.editImage = "{{asset('upload/banner')}}/" + data.image;
                alpineData.editLinkUrl = data.link_url || '';
                alpineData.editButtonText = data.button_text || '';
            } else {
                alpineData.editImage = "{{asset('upload/banner')}}/" + data;
                alpineData.editLinkUrl = '';
                alpineData.editButtonText = '';
            }
            alpineData.editModal = true;
        }
    });
}
</script>
@stop

