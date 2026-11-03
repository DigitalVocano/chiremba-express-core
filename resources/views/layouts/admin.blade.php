<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>@yield('title')</title>
    @yield('meta-data')

    <link rel="icon" href="{{Session::get('favicon')}}">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('admin_design/layouts/vertical/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- App CSS -->
    <link href="{{ asset('admin_design/layouts/vertical/assets/css/app.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- Icons CSS -->
    <link href="{{ asset('admin_design/layouts/vertical/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- DataTables CSS -->
    <link href="{{ asset('admin_design/layouts/vertical/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('admin_design/layouts/vertical/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('admin_design/layouts/vertical/assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
    <!-- Other CSS -->
    <link href="{{ asset('admin_design/layouts/vertical/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.css"/>

    <script type="text/javascript" src='https://maps.google.com/maps/api/js?key={{Config::get("mapdetail.key")}}&sensor=false&libraries=places'></script>

</head>
<body>

<div id="layout-wrapper">

    <header id="page-topbar">
        <!-- Topbar content goes here -->
    </header>

    <div class="vertical-menu">
        <!-- Sidebar content goes here -->
    </div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        <footer class="footer">
            <!-- Footer content goes here -->
        </footer>
    </div>
</div>

<!-- JavaScript includes -->
<script src="{{ asset('admin_design/layouts/vertical/assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin_design/layouts/vertical/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin_design/layouts/vertical/assets/libs/metismenu/metisMenu.min.js') }}"></script>
<script src="{{ asset('admin_design/layouts/vertical/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('admin_design/layouts/vertical/assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('admin_design/layouts/vertical/assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('admin_design/layouts/vertical/assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>

<script src="{{ asset('admin_design/layouts/vertical/assets/js/app.js') }}"></script>

@yield('footer')
</body>
</html>