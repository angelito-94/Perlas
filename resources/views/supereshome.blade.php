<!doctype html>
<html class="no-js" lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Finanzas </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Ela Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lykmapipo/themify-icons@0.1.2/css/themify-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pixeden-stroke-7-icon@1.2.3/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.2.0/css/flag-icon.min.css">
    <link rel="stylesheet" href="{{asset('assets/css/cs-skin-elastic.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/html5shiv/3.7.3/html5shiv.min.js"></script> -->
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.0/dist/chartist.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/weathericons@2.1.0/css/weather-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.9.0/dist/fullcalendar.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    <style>
        #weatherWidget .currentDesc {
            color: #ffffff !important;
        }

        .traffic-chart {
            min-height: 335px;
        }

        #flotPie1 {
            height: 150px;
        }

        #flotPie1 td {
            padding: 3px;
        }

        #flotPie1 table {
            top: 20px !important;
            right: -10px !important;
        }

        .chart-container {
            display: table;
            min-width: 270px;
            text-align: left;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        #flotLine5 {
            height: 105px;
        }

        #flotBarChart {
            height: 150px;
        }

        #cellPaiChart {
            height: 160px;
        }
    </style>
</head>

<body>
    <div id="loading-screen" style="display:none;">
        <img src="{{asset('images/spinning-circles.svg')}}">
    </div>

    <aside id="left-panel" class="left-panel ">
        <nav class="navbar navbar-expand-sm navbar-default ">
            <div id="main-menu" class="main-menu collapse navbar-collapse ">
                <ul class="nav navbar-nav">

                    <li class="active">
                        <a href="{{route('home')}}"><i class="menu-icon fa fa-laptop"></i>Administrador </a>
                    </li>

                    <!-- Menu Estados Financieros -->
                    <li class="menu-title">Estados Financieros</li>
                    <li><a href="{{route('superAdministradoresBalanceAnual')}}" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false"><i class="menu-icon fa fa-calendar"></i>Anual</a></li>

                    <!-- Menu Resumen -->
                    <li class="menu-title">Resumen</li>
                    <li><a href="{{route('superAdministradoresResumenBalanceAnual')}}" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false"><i class="menu-icon fa fa-file-text-o"></i>Anual</a></li>

                    <li class="menu-title">Indicadores</li>
                    <li><a href="{{route('superAdministradoresIndicadoresAnual')}}" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-bar-chart-o"></i>Anual</a></li>



                    <li class="menu-title">Registrar</li><!-- /.menu-title -->
                    <li>
                        <a href="{{route('superAdministradoresRegistrar')}}" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-plus"></i>Usuarios</a>
                    </li>
                    <li>
                        <a href="{{route('superAdministradoresAno')}}" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-plus"></i>Años</a>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>




    <div id="right-panel" class="right-panel">
        <!-- Header-->
        <header id="header" class="header" style="background: #B62440;">
            <div class="top-left ">
                <div class="navbar-header" style="background: #B62440;">
                    <a class="navbar-brand" href="{{url('home')}}"><img src="{{asset('images/logo.png')}}" alt="Logo"></a>
                    <a class="navbar-brand hidden" href="{{url('home')}}"><img src="{{asset('images/logo2.png')}}" alt="Logo"></a>
                    <a id="menuToggle" class="menutoggle"><i class="fa fa-bars"></i></a>
                </div>
            </div>
            <div class="top-right  ">
                <div class="header-menu ">
                    <div class="header-left">
                        <div class="dropdown for-message" style="color:white;">
                            {{auth()->user()->nombre}}
                        </div>
                    </div>

                    <div class="user-area dropdown float-right">
                        <a href="#" class="dropdown-toggle active" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="user-avatar rounded-circle" src="/storage/{{ Auth()->User()->foto }}" alt="User Avatar">
                        </a>

                        <div class="user-menu dropdown-menu" style="color:black;">

                            <a class="nav-link" href="{{route('InformacionAdministrador')}}"><i class="fa fa- user"></i>Mi Información</a>
                            <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                <i class="fa fa- user"></i>Cerrar Sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <div class="breadcrumbs">
            <div class="breadcrumbs-inner">
                <div class="row m-0">
                    <div class="col-sm-4">
                        <div class="page-header float-left">
                            <div class="page-title">
                                <h1>Administrador</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="page-header float-right">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    @yield('entiquetasBody')
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div id="center-panel" class="center-panel">
            <div class="content">
                <div class="animated fadeIn">
                    <div id="alert" class="alert alert-info" style="display:none;">
                    </div>
                    <div id="alertdanger" class="alert alert-danger" style="display:none;">
                    </div>
                    <div class="row">
                        @yield('contenido')
                    </div>
                </div>
            </div>
        </div>


        <div class="clearfix"></div>

        <footer class="site-footer">
            <div class="footer-inner bg-white">
                <div class="row">
                    <div class="col-sm-6">
                        Copyright &copy; 2020 chérubin.net <br>
                        <span class="fa fa-envelope"> cherubinyambay@gmail.com </span>
                    </div>                    
                    <div class="col-sm-6 text-right">
                        Diseñado por <a href="https://colorlib.com">Colorlib</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
    <script src="{{asset('assets/js/main.js')}}"></script>

    <!--  Chart js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.7.3/dist/Chart.bundle.min.js"></script>

    <!--Chartist Chart-->
    <script src="https://cdn.jsdelivr.net/npm/chartist@0.11.0/dist/chartist.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartist-plugin-legend@0.6.2/chartist-plugin-legend.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/jquery.flot@0.8.3/jquery.flot.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flot-pie@1.0.0/src/jquery.flot.pie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flot-spline@0.0.1/js/jquery.flot.spline.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/simpleweather@3.1.0/jquery.simpleWeather.min.js"></script>
    <script src="{{asset('assets/js/init/weather-init.js')}}"></script>

    <script src="https://cdn.jsdelivr.net/npm/moment@2.22.2/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.9.0/dist/fullcalendar.min.js"></script>
    <script src="{{asset('assets/js/init/fullcalendar-init.js')}}"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>

    <script src="{{asset('assets/js/lib/data-table/datatables.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/data-table/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/data-table/dataTables.buttons.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/data-table/buttons.bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/data-table/jszip.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/data-table/vfs_fonts.js')}}"></script>
    <script src="{{asset('assets/js/lib/data-table/buttons.html5.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/data-table/buttons.print.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/data-table/buttons.colVis.min.js')}}"></script>
    <script src="{{asset('assets/js/init/datatables-init.js')}}"></script>
    <script src="{{asset('assets/js/init/jquery-3.2.1.js')}}"></script>

    <br>
    <script src="http://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" defer></script>
    <br>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js" defer></script>

    <!-- Graficos -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"> </script>

</body>

</html>