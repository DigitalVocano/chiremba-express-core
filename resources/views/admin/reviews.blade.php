@extends('admin.layout')
@section('title')
{{("Reviews")}} | {{("Admin Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')

<div class="main-content">
   <div class="page-content p-4 md:p-6">
      <div class="container-fluid mx-auto space-y-6">

         <!-- Header Hero Card -->
         <div class="admin-header-card rounded-2xl p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
               <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-lime-400 text-2xl font-bold shadow-inner">
                     <i class="uil uil-star"></i>
                  </div>
                  <div>
                     <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{("Patient Reviews & Ratings")}}</h1>
                     <p class="text-xs md:text-sm text-blue-100/80 mt-1">{{("Monitor feedback, doctor star ratings, and patient testimonials across the platform.")}}</p>
                  </div>
               </div>
            </div>
         </div>

         <!-- Reviews Card -->
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

            <!-- Search Controls -->
            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
               <div class="relative flex-1 max-w-md">
                  <i class="uil uil-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                  <input type="text" id="reviewSearchInput" placeholder="{{('Search reviews by doctor, patient, or feedback content...')}}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent transition-all">
               </div>
            </div>

            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
               <table id="reviewtable" class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                  <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                     <tr>
                        <th scope="col" class="px-6 py-4 w-20">{{("ID")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Doctor")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Patient")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Rating")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Feedback / Description")}}</th>
                        <th scope="col" class="px-6 py-4 text-right w-24">{{("Action")}}</th>
                     </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700/60">
                     {{-- DataTables populated --}}
                  </tbody>
               </table>
            </div>

            <!-- Mobile View -->
            <div id="mobileCardsContainer" class="md:hidden space-y-4">
               {{-- Mobile cards --}}
            </div>

         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#reviewtable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ url("admin/reviewtable") }}',
            columns: [
                { 
                   data: 'id', 
                   name: 'id',
                   render: function(data) {
                      return '<span class="font-mono font-bold text-gray-900 dark:text-white">#' + data + '</span>';
                   }
                },
                { 
                   data: 'doctor_name', 
                   name: 'doctor_name',
                   render: function(data) {
                      return '<div class="font-bold text-gray-900 dark:text-white">' + (data || 'N/A') + '</div>';
                   }
                },
                { 
                   data: 'patient_name', 
                   name: 'patient_name',
                   render: function(data) {
                      return '<div class="text-gray-700 dark:text-gray-300">' + (data || 'Anonymous') + '</div>';
                   }
                },
                { 
                   data: 'rating', 
                   name: 'rating',
                   render: function(data) {
                      var stars = '';
                      var score = parseInt(data || 0);
                      for (var i = 1; i <= 5; i++) {
                         if (i <= score) {
                            stars += '<i class="fa-solid fa-star text-amber-400"></i>';
                         } else {
                            stars += '<i class="fa-regular fa-star text-gray-300 dark:text-gray-600"></i>';
                         }
                      }
                      return '<div class="flex items-center gap-1 text-sm">' + stars + ' <span class="font-bold ml-1 text-gray-800 dark:text-gray-200">(' + score + ')</span></div>';
                   }
                },
                { 
                   data: 'description', 
                   name: 'description',
                   render: function(data) {
                      return '<div class="max-w-xs text-xs text-gray-600 dark:text-gray-400 line-clamp-2">' + (data || 'No written comment.') + '</div>';
                   }
                },
                { 
                   data: 'action', 
                   name: 'action', 
                   orderable: false, 
                   searchable: false,
                   className: 'text-right' 
                }
            ],
            dom: 'rtip',
            drawCallback: function(settings) {
                renderMobileCards(settings.json ? settings.json.data : []);
            }
        });

        $('#reviewSearchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        function renderMobileCards(data) {
            var container = $('#mobileCardsContainer');
            container.empty();
            if (!data || data.length === 0) {
                container.html('<div class="p-6 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl">No reviews found.</div>');
                return;
            }
            $.each(data, function(i, item) {
                var score = parseInt(item.rating || 0);
                var stars = '';
                for (var s = 1; s <= 5; s++) {
                    stars += s <= score ? '★' : '☆';
                }
                var card = `
                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-mono font-bold text-gray-900 dark:text-white text-sm">#${item.id}</span>
                            <span class="text-amber-500 font-bold text-sm">${stars} (${score})</span>
                        </div>
                        <div class="space-y-1 text-xs">
                            <p class="font-bold text-gray-900 dark:text-white"><i class="uil uil-user-md text-lime-500 mr-1"></i> Doctor: ${item.doctor_name || 'N/A'}</p>
                            <p class="text-gray-600 dark:text-gray-400"><i class="uil uil-user mr-1"></i> Patient: ${item.patient_name || 'Anonymous'}</p>
                            <p class="text-gray-700 dark:text-gray-300 italic pt-1">"${item.description || 'No comment'}"</p>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }
    });
</script>
@stop
