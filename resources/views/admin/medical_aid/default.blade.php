@extends('admin.layout')
@section('title')
Medical Aid Societies | Admin
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Medical Aid Societies</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               Manage Zimbabwe Medical Aid Societies
            </div>
         </div>

         <div x-data="{ addModal: false, editModal: false, editId: null, editName: '', editCode: '', editDescription: '', editActive: true }">
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
               @if(Session::has('message'))
               <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                  <span>{{ Session::get('message') }}</span>
                  <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
               </div>
               @endif

               <div class="flex justify-between items-center mb-4">
                  <h4 class="text-xl font-semibold text-gray-800 dark:text-white">Medical Aid List</h4>
                  <button @click="addModal = true" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring transition ease-in-out duration-150">Add Medical Aid</button>
               </div>

               <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                     <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Society Name</th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                           <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                        </tr>
                     </thead>
                     <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($societies as $item)
                        <tr>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->id }}</td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><span class="px-2 py-1 bg-gray-100 rounded text-xs font-mono">{{ $item->code }}</span></td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm">
                              @if($item->is_active)
                                 <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Active</span>
                              @else
                                 <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Inactive</span>
                              @endif
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                              <button @click="editId = '{{ $item->id }}'; editName = '{{ addslashes($item->name) }}'; editCode = '{{ addslashes($item->code) }}'; editDescription = '{{ addslashes($item->description) }}'; editActive = {{ $item->is_active ? 'true' : 'false' }}; editModal = true" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                              <a href="{{ url('admin/delete-medical-aid/'.$item->id) }}" onclick="return confirm('Are you sure you want to delete this Medical Aid Society?')" class="text-red-600 hover:text-red-900">Delete</a>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>

            <!-- Add Modal -->
            <div x-show="addModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
               <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                  <div class="fixed inset-0 transition-opacity" @click="addModal = false">
                     <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                  </div>
                  <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                     <form action="{{ url('admin/save-medical-aid') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-6">
                           <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add Medical Aid Society</h3>
                           <div class="mb-4">
                              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Society Name *</label>
                              <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" placeholder="e.g. CIMAS Medical Aid Society">
                           </div>
                           <div class="mb-4">
                              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Short Code</label>
                              <input type="text" name="code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" placeholder="e.g. CIMAS">
                           </div>
                           <div class="mb-4">
                              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                              <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" placeholder="Optional notes or claim instructions"></textarea>
                           </div>
                           <div class="mb-4 flex items-center">
                              <input type="checkbox" name="is_active" id="is_active_add" value="1" checked class="h-4 w-4 text-primary-600 border-gray-300 rounded">
                              <label for="is_active_add" class="ml-2 block text-sm text-gray-900 dark:text-white">Active for Patients & Providers</label>
                           </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                           <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                           <button type="button" @click="addModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">Cancel</button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>

            <!-- Edit Modal -->
            <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
               <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                  <div class="fixed inset-0 transition-opacity" @click="editModal = false">
                     <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                  </div>
                  <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                     <form action="{{ url('admin/update-medical-aid') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" :value="editId">
                        <div class="p-6">
                           <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit Medical Aid Society</h3>
                           <div class="mb-4">
                              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Society Name *</label>
                              <input type="text" name="name" :value="editName" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                           </div>
                           <div class="mb-4">
                              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Short Code</label>
                              <input type="text" name="code" :value="editCode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                           </div>
                           <div class="mb-4">
                              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                              <textarea name="description" rows="2" x-text="editDescription" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"></textarea>
                           </div>
                           <div class="mb-4 flex items-center">
                              <input type="checkbox" name="is_active" id="is_active_edit" value="1" :checked="editActive" class="h-4 w-4 text-primary-600 border-gray-300 rounded">
                              <label for="is_active_edit" class="ml-2 block text-sm text-gray-900 dark:text-white">Active for Patients & Providers</label>
                           </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                           <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Update</button>
                           <button type="button" @click="editModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">Cancel</button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@stop
