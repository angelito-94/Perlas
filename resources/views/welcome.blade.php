<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="cherubin.net" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <link rel="shortcut icon" href="{{asset('ftco-32x32.png')}}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('fonts/icomoon/style.css')}}">
    <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/animate.css')}}">
    <link rel="stylesheet" href="{{asset('css/owl.carousel.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/owl.theme.default.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/bootstrap-datepicker.css')}}">
    <link rel="stylesheet" href="{{asset('fonts/flaticon/font/flaticon.css')}}">
    <link rel="stylesheet" href="{{asset('css/aos.css')}}">
    <link rel="stylesheet" href="{{asset('css/jquery.fancybox.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
</head>

<body data-spy="scroll" data-target=".site-navbar-target" data-offset="300">
    <div class="site-wrap">

        <div class="site-mobile-menu site-navbar-target">
            <div class="site-mobile-menu-header">
                <div class="site-mobile-menu-close mt-3">
                    <span class="icon-close2 js-menu-toggle"></span>
                </div>
            </div>
            <div class="site-mobile-menu-body"></div>
        </div>

        <div class="site-navbar-wrap">
            <div class="site-navbar site-navbar-target js-sticky-header">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-6 col-md-4">
                            <h1 class="my-0 site-logo"><a href="{{ url('/') }}">Finanzas<span class="text-primary"></span> </a></h1>
                        </div>
                        <div class="col-6 col-md-8">
                            <nav class="site-navigation text-right" role="navigation">
                                <div class="container">
                                    <div class="d-inline-block d-lg-block ml-md-0 mr-auto py-3"><a href="#" class="site-menu-toggle js-menu-toggle text-black">
                                            <span class="icon-menu h3"></span> <span class="menu-text">Menu</span>
                                        </a></div>
                                    <ul class="site-menu main-menu js-clone-nav d-none d-lg-none">
                                        <li><a href="#home-section" class="nav-link">Inicio</a></li>
                                        @guest
                                        <li><a href="{{ route('login') }}" class="nav-link">Logueate</a></li>
                                        <li><a href="{{ route('register') }}" class="nav-link">Registrate</a></li>
                                        @else
                                        <li><a href="{{ route('home') }}" class="nav-link">Tu cuenta</a></li>
                                        @endguest
                                        <li><a href="#contact-section" class="nav-link">Contactanos</a></li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-blocks-cover" id="home-section">
            <div class="img-wrap">
                <div class="owl-carousel slide-one-item hero-slider">
                    <div class="slide">
                        <img src="{{asset('images/finanzas1.jpg')}}" alt="Free Website Template by Free-Template.co">
                    </div>
                    <div class="slide">
                        <img src="{{asset('images/finanzas2.jpg')}}" alt="Free Website Template by Free-Template.co">
                    </div>
                    <div class="slide">
                        <img src="{{asset('images/finanzas3.jpg')}}" alt="Free Website Template by Free-Template.co">
                    </div>
                    <div class="slide">
                        <img src="{{asset('images/finanzas4.jpg')}}" alt="Free Website Template by Free-Template.co">
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-md-6 ml-auto align-self-center">
                        <div class="intro">
                            <div class="heading">
                                <h1>Finanzas</h1>
                            </div>
                            <div class="text">
                                <p class="sub-text mb-5">Un nuevo sistema hecho para ti</a></p>
                                @guest
                                <p><a href="{{ route('login') }}"  class="btn btn-outline-primary btn-md">Logueate</a></p>
                                @else
                                <p><a href="{{ route('home') }}"  class="btn btn-outline-primary btn-md">Tu cuenta</a></p>
                                @endguest
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="site-section" id="contact-section">
            <div class="container">
                <form action=""  class="contact-form" enctype="multipart/form-data">

                    <div class="section-title text-center mb-5">
                        <h2 class="title text-primary">Contactanos</h2>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre">
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <textarea class="form-control" name="mensaje" id="mensaje" cols="30" rows="5" placeholder="Mensaje"></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-md">Enviar Mensaje</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <footer class="site-footer">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <a href="#" class="pl-0 pr-3"><span class="icon-facebook"></span></a>
                            <a href="#" class="pl-3 pr-3"><span class="icon-twitter"></span></a>
                            <a href="#" class="pl-3 pr-3"><span class="icon-instagram"></span></a>
                            <a href="#" class="pl-3 pr-3"><span class="icon-linkedin"></span></a>
                        </div>
                        <p>
                            <small class="block">&copy; 2019 <strong class="text-white">chérubin.net</strong> Todos los derechos reservados. <br>Contactos: <strong class="text-white">cherubinyambay@gmail.com , +593994408123 </strong>Ecuador<br> Diseñado por <a href="https://free-template.co/" target="_blank">Free-Template.co</a></small>
                        </p>
                    </div>

                </div>
            </div>
        </footer>
    </div>



    <!-- <div class="flex-center position-ref full-height">
        @if (Route::has('login'))
        <div class="top-right links">
            @auth
            <a href="{{ url('/home') }}">Home</a>
            @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
        @endif
        </div>
-->
    <script src="{{asset('js/jquery-3.3.1.min.js')}}"></script>
    <script src="{{asset('js/popper.min.js')}}"></script>
    <script src="{{asset('js/bootstrap.min.js')}}"></script>
    <script src="{{asset('js/owl.carousel.min.js')}}"></script>
    <script src="{{asset('js/aos.js')}}"></script>
    <script src="{{asset('js/jquery.sticky.js')}}"></script>
    <script src="{{asset('js/stickyfill.min.js')}}"></script>
    <script src="{{asset('js/jquery.easing.1.3.js')}}"></script>
    <script src="{{asset('js/isotope.pkgd.min.js')}}"></script>

    <script src="{{asset('js/jquery.fancybox.min.js')}}"></script>
    <script src="{{asset('js/main.js')}}"></script>
</body>

</html>