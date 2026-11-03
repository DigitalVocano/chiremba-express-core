@extends('admin.layout')
@section('title')
{{("Patients")}} | {{("Admin")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-4 sm:p-6">
      <div class="container-fluid mx-auto space-y-6">

         <!-- Admin Header Hero Card -->
         <div class="admin-header-card rounded-3xl p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center space-x-4">
               <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20 text-lime-400 text-2xl shadow-inner">
                  <i class="fa-solid fa-hospital-user"></i>
               </div>
               <div>
                  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{("Patients Directory")}}</h1>
                  <p class="text-xs sm:text-sm text-blue-100/80 mt-1">Manage and view registered patient profiles across the platform</p>
               </div>
            </div>
            <div class="flex items-center space-x-2 text-xs bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/20 self-start sm:self-auto">
               <a href="{{url('admin/dashboard')}}" class="hover:text-lime-300 transition-colors">Home</a>
               <span class="text-white/40">/</span>
               <span class="font-semibold text-lime-400">Patients</span>
            </div>
         </div>

         <!-- Main Content Card -->
         <div class="bg-white dark:bg-gray-800 shadow-xl rounded-3xl p-6 border border-gray-100 dark:border-gray-700">
            @if(Session::has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/50 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex justify-between items-center" role="alert">
               <div class="flex items-center space-x-2">
                   <i class="fa-solid fa-circle-check text-emerald-500"></i>
                   <span>{{ Session::get('message') }}</span>
               </div>
               <button @click="show = false" class="text-emerald-900 dark:text-emerald-300 font-bold">&times;</button>
            </div>
            @endif

            <!-- Search & Filter Bar -->
            <form id="filter-form" class="mb-6 bg-gray-50 dark:bg-gray-700/40 p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label for="search_term" class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">Search Patients</label>
                        <div class="relative">
                           <input type="text" name="search_term" id="search_term" placeholder="Name, Email, or Phone..." class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-200 shadow-sm focus:border-lime-500 focus:ring-lime-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                           <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    <div>
                        <label for="sort_by" class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">Sort Order</label>
                        <select id="sort_by" name="sort_by" class="block w-full px-4 py-2.5 rounded-xl border-gray-200 shadow-sm focus:border-lime-500 focus:ring-lime-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" id="reset-btn" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm transition-all duration-200">
                           <i class="fa-solid fa-rotate mr-1.5"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </form>

            <!-- Desktop Full-Width Table View (hidden on mobile < md) -->
            <div class="hidden md:block w-full overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700">
               <table id="userstable" class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                  <thead class="bg-gray-50 dark:bg-gray-700/60">
                     <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Patient Name")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Email")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Phone")}}</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider pr-6">{{("Action")}}</th>
                     </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                     {{-- DataTables will populate tbody --}}
                  </tbody>
               </table>
            </div>

            <!-- Mobile Card View Container (visible on mobile < md) -->
            <div id="mobileCardsContainer" class="block md:hidden space-y-4">
               <!-- Mobile cards populated via JS on table redraw -->
            </div>

         </div>
      </div>
   </div>
</div>
@stop

@section('footer')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    function getInitials(name) {
        if (!name) return '?';
        var parts = name.trim().split(/\s+/);
        if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
        return parts[0].substring(0, 2).toUpperCase();
    }

    function getColor(name) {
        if (!name) return '#0A2B68';
        var hash = 0;
        for (var i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        var colors = ['#0A2B68', '#10B981', '#8B5CF6', '#EF4444', '#F59E0B', '#06B6D4', '#6366F1', '#EC4899'];
        return colors[Math.abs(hash) % colors.length];
    }

    $(document).ready(function() {
        var table = $('#userstable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            width: "100%",
            ajax: {
                url: '{{ route("admin.patients.table") }}',
                data: function (d) {
                    d.search_term = $('#search_term').val();
                    d.sort_by = $('#sort_by').val();
                }
            },
            columns: [
                { data: 'id', name: 'id', width: '10%', className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-semibold' },
                { 
                    data: 'name', 
                    name: 'name',
                    width: '35%',
                    render: function(data, type, full, meta) {
                        var name = data || 'Patient';
                        var initials = getInitials(name);
                        var bg = getColor(name);
                        var imgHtml = (full.avatar && full.avatar.length > 5) 
                            ? '<img src="' + full.avatar + '" class="w-10 h-10 rounded-full object-cover shadow-md border border-gray-100" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"><div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow-md text-xs" style="background:' + bg + '; display:none;">' + initials + '</div>'
                            : '<div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow-md text-xs" style="background:' + bg + '">' + initials + '</div>';
                        
                        return '<div class="flex items-center space-x-3">' +
                               imgHtml +
                               '<div><div class="font-bold text-gray-900 dark:text-white">' + name + '</div></div>' +
                               '</div>';
                    }
                },
                { data: 'email', name: 'email', width: '25%', className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300' },
                { data: 'phone', name: 'phone', width: '18%', className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '12%', className: 'px-6 py-4 whitespace-nowrap text-right text-sm font-medium pr-6' }
            ],
            dom: 'lrtip',
            drawCallback: function(settings) {
                // Populate mobile cards view on redraw
                var api = this.api();
                var data = api.rows({ page: 'current' }).data();
                var container = $('#mobileCardsContainer');
                container.empty();

                if (data.length === 0) {
                    container.append('<div class="p-6 text-center text-gray-500 bg-gray-50 rounded-2xl">No patient records found.</div>');
                    return;
                }

                data.each(function(row) {
                    var name = row.name || 'Patient';
                    var initials = getInitials(name);
                    var bg = getColor(name);
                    var avatarHtml = (row.avatar && row.avatar.length > 5)
                        ? '<img src="' + row.avatar + '" class="w-12 h-12 rounded-full object-cover shadow-sm" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"><div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-sm shadow-sm" style="background:' + bg + '; display:none;">' + initials + '</div>'
                        : '<div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-sm shadow-sm" style="background:' + bg + '">' + initials + '</div>';

                    var cardHtml = `
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    ${avatarHtml}
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white text-base">${name}</div>
                                        <div class="text-xs text-gray-400">ID: #${row.id}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs space-y-1 pt-2 border-t border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-300">
                                <div><i class="fa-regular fa-envelope text-blue-900 mr-2"></i><strong>Email:</strong> ${row.email || 'N/A'}</div>
                                <div><i class="fa-solid fa-phone text-lime-600 mr-2"></i><strong>Phone:</strong> ${row.phone || 'N/A'}</div>
                            </div>
                            <div class="pt-3 flex justify-end items-center border-t border-gray-100 dark:border-gray-700">
                                ${row.action}
                            </div>
                        </div>
                    `;
                    container.append(cardHtml);
                });
            }
        });

        // Redraw table on filter change
        $('#search_term, #sort_by').on('change keyup', function() {
            table.draw();
        });

        // Reset button functionality
        $('#reset-btn').on('click', function() {
            $('#filter-form')[0].reset();
            table.draw();
        });
    });

    function delete_record(url) {
        if (confirm('Are you sure you want to delete this record?')) {
            window.location.href = url;
        }
    }
</script>
@stop
