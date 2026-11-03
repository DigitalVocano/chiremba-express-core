@extends('admin.layout')
@section('title')
{{("Banner")}} | {{("Admin Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-4 md:p-6">
      <div class="container-fluid mx-auto space-y-6">

         <div x-data="bannerManager()" @open-edit-modal.window="openEditModal($event.detail)">

            <!-- Header Hero Card -->
            <div class="admin-header-card rounded-2xl p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
               <div>
                  <div class="flex items-center gap-3">
                     <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-lime-400 text-2xl font-bold shadow-inner">
                        <i class="uil uil-image"></i>
                     </div>
                     <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{("Promotional Banners")}}</h1>
                        <p class="text-xs md:text-sm text-blue-100/80 mt-1">{{("Manage hero slider images and promotional offers displayed on patient mobile apps and portal.")}}</p>
                     </div>
                  </div>
               </div>
               <button @click="addModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-600 hover:to-emerald-600 text-gray-900 font-bold text-sm rounded-xl shadow-lg hover:shadow-lime-500/25 transition-all">
                  <i class="uil uil-plus text-lg"></i>
                  <span>{{("Add Banner")}}</span>
               </button>
            </div>

            <!-- Banner List Card -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-4 md:p-6 border border-gray-100 dark:border-gray-700">
               @if(Session::has('message'))
               <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex justify-between items-center" role="alert">
                  <div class="flex items-center gap-2">
                      <i class="uil uil-check-circle text-lg"></i>
                      <span class="font-medium">{{ Session::get('message') }}</span>
                  </div>
                  <button @click="show = false" class="text-emerald-900 dark:text-emerald-300 font-bold">&times;</button>
               </div>
               @endif

               <!-- Desktop View -->
               <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                  <table id="bannertable" class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                     <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                        <tr>
                           <th scope="col" class="px-6 py-4 w-20">{{("ID")}}</th>
                           <th scope="col" class="px-6 py-4">{{("Banner Preview")}}</th>
                           <th scope="col" class="px-6 py-4 text-right w-36">{{("Actions")}}</th>
                        </tr>
                     </thead>
                     <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700/60">
                        {{-- AJAX Populated --}}
                     </tbody>
                  </table>
               </div>

               <!-- Mobile Card View -->
               <div id="mobileCardsContainer" class="md:hidden space-y-4">
                  {{-- Mobile cards --}}
               </div>
            </div>

            <!-- Add Banner Modal -->
            <div x-show="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" x-cloak>
               <div class="relative w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.away="addModal = false">
                  <div class="p-5 bg-gradient-to-r from-navy-900 to-blue-900 text-white flex justify-between items-center" style="background: linear-gradient(135deg, #0A2B68 0%, #1E3A8A 100%);">
                     <div class="flex items-center gap-2">
                        <i class="uil uil-plus-circle text-lime-400 text-xl"></i>
                        <h5 class="text-lg font-bold text-white">{{("Add New Banner")}}</h5>
                     </div>
                     <button @click="addModal = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
                  </div>
                  <form action="{{url('admin/savebanner')}}" method="post" enctype="multipart/form-data" class="p-6 space-y-4">
                     {{csrf_field()}}
                     <div>
                        <label for="upload_file_add" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{("Upload Banner Image(s)")}}</label>
                        <input type="file" id="upload_file_add" name="image[]" multiple required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-lime-100 file:text-lime-800 hover:file:bg-lime-200 dark:file:bg-lime-900/40 dark:file:text-lime-300 transition-all border border-gray-200 dark:border-gray-700 rounded-xl">
                     </div>
                     <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="addModal = false" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">{{('Close')}}</button>
                        <button type="submit" class="px-5 py-2.5 bg-lime-500 hover:bg-lime-600 text-gray-900 font-bold text-sm rounded-xl shadow-md transition-all">{{("Upload Banner")}}</button>
                     </div>
                  </form>
               </div>
            </div>

            <!-- Edit Banner Modal -->
            <div x-show="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" x-cloak>
               <div class="relative w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.away="closeEditModal()">
                  <div class="p-5 bg-gradient-to-r from-navy-900 to-blue-900 text-white flex justify-between items-center" style="background: linear-gradient(135deg, #0A2B68 0%, #1E3A8A 100%);">
                     <div class="flex items-center gap-2">
                        <i class="uil uil-pen text-lime-400 text-xl"></i>
                        <h5 class="text-lg font-bold text-white">{{("Edit Banner")}}</h5>
                     </div>
                     <button @click="closeEditModal()" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
                  </div>
                  <form :action="editFormUrl" method="post" enctype="multipart/form-data" class="p-6 space-y-4">
                     {{csrf_field()}}
                     <input type="hidden" name="id" :value="banner.id">
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{("Current Image")}}</label>
                        <div class="p-2 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 mb-3">
                           <img :src="banner.imageUrl" class="w-full h-40 object-contain rounded-lg" alt="Current Banner">
                        </div>
                        <label for="upload_file_edit" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{("Replace Image")}}</label>
                        <input type="file" id="upload_file_edit" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-lime-100 file:text-lime-800 hover:file:bg-lime-200 dark:file:bg-lime-900/40 dark:file:text-lime-300 transition-all border border-gray-200 dark:border-gray-700 rounded-xl">
                     </div>
                     <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">{{('Close')}}</button>
                        <button type="submit" class="px-5 py-2.5 bg-lime-500 hover:bg-lime-600 text-gray-900 font-bold text-sm rounded-xl shadow-md transition-all">{{("Update Banner")}}</button>
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
@parent
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    function bannerManager() {
        return {
            addModal: false,
            editModal: false,
            banner: {
                id: null,
                imageUrl: ''
            },
            editFormUrl: '',
            openEditModal(detail) {
                this.banner.id = detail.id;
                this.banner.imageUrl = detail.imageUrl;
                this.editFormUrl = `{{ url('admin/updatebanner') }}`;
                this.editModal = true;
            },
            closeEditModal() {
                this.editModal = false;
                this.banner = { id: null, imageUrl: '' };
                this.editFormUrl = '';
            }
        }
    }

    $(document).ready(function() {
        var table = $('#bannertable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ url("admin/bannertable") }}',
            columns: [
                { 
                   data: 'id', 
                   name: 'id',
                   render: function(data) {
                      return '<span class="font-mono font-bold text-gray-900 dark:text-white">#' + data + '</span>';
                   }
                },
                { 
                    data: 'image_url', 
                    name: 'image', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        return `<img src="${data}" class="h-20 w-auto object-cover rounded-xl shadow-sm border border-gray-200 dark:border-gray-700" alt="Banner">`;
                    }
                },
                { 
                    data: 'id', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-right',
                    render: function(data, type, row) {
                        const editButton = `
                            <button 
                                type="button" 
                                @click="$dispatch('open-edit-modal', { id: ${row.id}, imageUrl: '${row.image_url}' })" 
                                class="p-2 rounded-xl text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 text-lg transition-colors"
                                title="Edit Banner">
                                <i class="uil uil-pen"></i>
                            </button>`;

                        const deleteButton = `
                            <a href="javascript:void(0);" onclick="delete_record('${row.id}', '{{ url('admin/deletebanner') }}')" class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-gray-700 text-lg transition-colors" title="Delete Banner">
                                <i class="uil uil-trash-alt"></i>
                            </a>`;

                        return `<div class="flex items-center justify-end gap-1">${editButton}${deleteButton}</div>`;
                    }
                }
            ],
            drawCallback: function( settings ) {
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(this.api().table().body());
                }
                renderMobileCards(settings.json ? settings.json.data : []);
            },
            dom: 'rtip'
        });

        function renderMobileCards(data) {
            var container = $('#mobileCardsContainer');
            container.empty();
            if (!data || data.length === 0) {
                container.html('<div class="p-6 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl">No banners found.</div>');
                return;
            }
            $.each(data, function(i, item) {
                var card = `
                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-mono font-bold text-gray-900 dark:text-white text-sm">Banner #${item.id}</span>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: { id: ${item.id}, imageUrl: '${item.image_url}' } }))" class="p-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 text-sm font-semibold">Edit</button>
                                <a href="javascript:void(0);" onclick="delete_record('${item.id}', '{{ url('admin/deletebanner') }}')" class="p-1.5 rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 text-sm font-semibold">Delete</a>
                            </div>
                        </div>
                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                            <img src="${item.image_url}" class="w-full h-32 object-cover" alt="Banner Preview">
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }
    });
</script>
@stop
