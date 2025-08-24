@vite('resources/css/welcome.css')


<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="//db.onlinewebfonts.com/c/3d85dc16e94da9358d451666fdbc3398?family=Circular+Medium" rel="stylesheet" type="text/css"/>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <title>AURA</title>
    </head>

    
      @yield('content')
@include('components.traductor')


    <body>
      <div class="bg-main">
       <div class="bg-main-img"></div>
      </div>
      
      <header class="navbar navbar-dark navbar-fixed-top" role="banner">
             <div class="container">
                <div class="navbar-header">
                    <a href="#">
                        <span class="navbar-logo">Aura</span>
                    </a>
                </div>

                <nav class="collapse navbar-collapse" id="navbar-nav" role="">
                     <ul class="nav navbar-nav navbar-right nav-main">
    <li class="hidden">
        <a href="../html/menu.php" id="nav-link-Explore">menu</a>
    </li>

    <li>
        <a href="mailto:estudiante20240341@cdb.edu.sv?subject=Consulta%20sobre%20AURA" class="nav-btn" id="nav-link-help">Ayuda</a>

    </li>

    <li>
        <a href="login" class="nav-btn" id="header-login-link">Iniciar Sesión</a>
    </li>

    <li>
        <a href="seleccionar-tipo" class="nav-btn" id="header-register-link">Registrarse</a>
    </li>
</ul>
                </nav>
            </div>
        </header>
      
      <div class="jumbotron">
            <div class="container">
                <div id="carousel-tour" class="carousel slide" data-ride="carousel" data-interval="false">

                    <!-- Indicators -->
                    <ol class="carousel-indicators">
                        <li data-target="#carousel-tour" data-slide-to="0" class="active js-button-hero-carousel-dots"></li>
                        <li data-target="#carousel-tour" data-slide-to="1" class="js-button-hero-carousel-dots"></li>
                        <li data-target="#carousel-tour" data-slide-to="2" class="js-button-hero-carousel-dots"></li>
                        <li data-target="#carousel-tour" data-slide-to="3" class="js-button-hero-carousel-dots"></li>
                    </ol>
                    <div class="carousel-inner" role="listbox" style="height: 307px;">
                        <div class="item active">
                            <h1>
                                <span class="text-white">
                                    3 Meses Premium por $0.99.
                                </span>
                            </h1>
                            <a href="../AURA_V4/html/register.php" class="btn btn-lg btn-solid js-button-hero-get-premium">
                                Leer más
                            </a>
                            <p class="text-center text-white text-signup">
                                O regístrate en nuestro <a href="../AURA_V4/html/register.php" class="js-goto-signup">servicio gratuito</a>
                            </p>
                        </div>
                        
                        <div class="item">
                            <h1>
                                <span class="text-white">
                                    Plan Premium familiar por<br>por solo $14.00.
                                </span>
                            </h1>
                            <a href="../AURA_V4/html/register.php" class="btn btn-lg btn-solid js-button-hero-family">
                                Leer más
                            </a>
                        </div>

                        <div class="item">
                            <h1>
                                <span class="text-white">
                                    Los estudiantes tienen un 50% de descunento en plan premium
                                </span>
                            </h1>
                            <a href="../AURA_V4/html/register.php" class="btn btn-lg btn-solid js-button-hero-premium">
                                Leer más
                            </a>
                        </div>

                        <div class="item">
                            <h1>
                                <span class="text-white">
                                   Escucha AURA en todas partes y se parte de una grandiosa experiencia musical
                                </span>
                            </h1>
                            <a href="../AURA_V4/html/register.php" class="btn btn-lg btn-solid js-button-hero-playstation">
                                Leer más 
                            </a>
                        </div>
                    </div>

                     <!--Controls-->
                     <a class="left carousel-control js-button-hero-carousel-left-arrow" href="#carousel-tour" data-slide="prev">
                        <span class="icon-prev"></span>
                        <span class="sr-only">Previous</span>
                     </a>
                     <a class="right carousel-control js-button-hero-carousel-right-arrow" href="#carousel-tour" data-slide="next">
                        <span class="icon-next"></span>
                        <span class="sr-only">Next</span>  
                     </a>
                </div>
            </div>
        </div>

      <div class="container">
        <a class="btn-scroll" name="whats-on-spotify" href="#whats-on-spotify">
          <span class="btn-scroll-text">Leer más acerca de AURA</span>
        </a>
      </div>
      
      <div class="jumbotron jumbotron-whats-on bg-white">
            <div class="container">
                <div class="row">
                    <div class="col-sm-7 col-sm-push-5 col-md-5 col-md-push-7">
                        <h2 class="text-klein-purple">
                        Que es AURA?
                        </h2>
                        <div>
                            <h3 class="text-klein-purple">
                            Música
                            </h3>
                            <p>
                                Hay millones de canciones en Aura. Escucha tus favoritas, descubre nuevos temas y apoya a los artistas salvadoreños que están comenzando.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-klein-purple">
                                Playlists
                            </h3>
                            <p>
                                Álbumes de tus artistas favoritos, con sugerencias de artistas salvadoreños y talentos similares según tus gustos.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-klein-purple">
                                Nuevos lanzamientos
                            </h3>
                            <p>
                                Los nuevos lanzamientos que no te puedes perder: descubre la música más reciente de artistas salvadoreños y encuentra tu próxima canción favorita.
                            </p>
                        </div>
                    </div>

                    <div class="col-sm-5 col-sm-pull-7 col-md-6 col-md-pull-5">
                        <div class="albums row">
                            <img src="../AURA_V4//img/2l.jpg"
                            class="album-img col-sm-6"/>

                            <img src="../AURA_V4/img/1l.webp"
                                class="album-img col-sm-6"/>
                            <img src="../AURA_V4/img/3l.avif"
                                class="album-img col-sm-6"/>
                            <img src="../AURA_V4/img/l4.jpg"
                                class="album-img col-sm-6"/>
                         </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="jumbotron">
            <div class="container">
                <div class="row">
                    <div class="col-sm-4 text-lef text-white">
                        <div>
                            <h2>
                                Es fácil.
                            </h2>
                        </div>
                        <div>
                            <h3 class="text-aquamarine">
                                Buscar
                            </h3>
                            <p>
                                ¿Ya sabes qué quieres escuchar? Solo búscalo y dale play.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-aquamarine">
                                Explorar
                            </h3>
                            <p>
                                Revisa los rankings más recientes, nuevos lanzamientos de artistas salvadoreños y playlists creadas para ti.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-aquamarine">
                                Descubrir
                            </h3>
                            <p>
                                Cada semana, disfruta música nueva con tu playlist personalizada o relájate escuchando Radio, descubriendo talentos salvadoreños y artistas similares a tus gustos.
                            </p>
                        </div>
                    </div>
                    <div class="devices-container grid-overflow">
                        <div class="row">
                            <div class="col-xs-4">
                                <div class="iphone iphone-1">
                                    <img src="../AURA_V4/img/fgdsgrsdgsrd.jpg">
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="iphone iphone-2">
                                    <img src="../AURA_V4/img/iqqaj3p3t4w61.jpg">
                                </div>
                                <div class="iphone iphone-3">
                                    <img src="https://imag.malavida.com/mvimgbig/download-fs/spotify-10503-10.jpg">
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="iphone iphone-4">
                                    <img src="https://imag.malavida.com/mvimgbig/download-fs/spotify-10503-4.jpg">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- section: all devices -->
        <div class="jumbotron jumbotron-devices">
            <div class="container">
                <img class="center-block" src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fc/Different_devices_light.svg/748px-Different_devices_light.svg.png?20170306214050">
                <div>
                    <h2>
                        <span class="text-white">
                            Una cuenta. Escucha en todos lados.
                        </span>
                    </h2>
                </div>
                <ul class="device-list text-white">
                    <li>MÓVIL</li>
                    <li>COMPUTADORA</li>
                    <li>TABLETA</li>
                    <li>PARLANTE</li>
                    <li> SITIO WEB</li>
                </ul>
            </div>
        </div>

        <div class="jumbotron jumbotron-subscriptions">
            <div class="container">
                <div class="row">
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-sm-10 col-lg-8">
                                <div>
                                    <h1>
                                        <span class="text-white">
                                            Ve por la música.
                                        </span>
                                    </h1>
                                </div>
                                <div>
                                    <p class="text-white">
                                        Escucha gratis.
                                        <br>
                                        O hazte Premium para escuchar a demanda, en cualquier lugar.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-10">
                                <div>
                                    <h3 class="price-title text-aquamarine">AURA</h3>
                                    <h3 class="price text-white">
                                        $0.00 <span class="month text-aquamarine"> / MES</span>                                        
                                    </h3>
                                    <div class="clearfix"></div>
                                    <ul class="details-subscriptions text-white">
                                        <li>
                                            Disfruta tus álbumes y artistas favoritos, con anuncios ocasionales.
                                        </li>
                                    </ul>
                                    <a href="../AURA_V4/html/register.php" class="btn btn-solid">
                                        OBTEN AURA GRATIS
                                    </a>
                                </div>
                            
                            <div class="clearfix"></div>



                                <div class="animated animatedFadeInUp fadeInUp">
                                    <h3 class="price-title text-aquamarine">
                                        AURA Premium
                                    </h3>
                                    <h3 class="price text-white">
                                        $0.99 <span class="month text-aquamarine"> /3 MESES</span>
                                    </h3>
                                    <div class="clearfix"></div>
                                    <div class="tour-variation">
                                        <p class="h3-sub text-white"><a href="/us/family">Ofertas para Familiares </a> y <a href="/us/studants">Estudiantes</a> disponibles</p>
                                        
                                        <p class="h3-sub text-white">Solo $9.99/mes después.</p>
                                    </div>
                                    <div class="clearfix"></div>

                                    <ul class="details-subscriptions text-white">
                                        <li>
                                            Escucha a demanda.
                                        </li>
                                        <li>
                                            Escucha sin conexión.
                                        </li>
                                        <li>
                                            Sin anuncios.
                                        </li>
                                        <li>
                                            Audio de alta calidad.
                                        </li>
                                    </ul>
                                    <a href="#" class="btn btn-solid">
                                        OBTEN AURA PREMIUM
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid-overflow hidden-xs hidden-sm">
                        <div class="animated animatedFadeInUp fadeInUp">
                            <div class="nexus">
                                <img src="../AURA_V4/img/kizao.webp">
                            </div>
                        </div>
                    </div>                        
                </div>
                <p class="text-center legal text-white">
                    Solo $9.99/mes después. Oferta no disponible para usuarios.
                </p>
            </div>
        </div>

        <!-- section: footer -->
        <footer role="contentinfo" class="footer footer-highlight-aquamarine">
            <div class="container">
                <nav class="row">
        <!-- section: footer: icon -->
                    <div class="col-xs-12 col-md-2">
                        <div class="footer-logo">
                            <a href="#">AURA</a>
                        </div>
                    </div>
        <!-- section: footer: 1st column -->
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <h3 class="nav-title">EMPRESA</h3>
                        <ul class="nav">
                            <li>
                                <a href="#" id="nav-link-about">
                                    Acerca de
                                </a>
                            </li>
                            <li>
                                <a href="#" id="nav-link-jobs">
                                    Empleos
                                </a>
                            </li>
                            <li>
                                <a href="#" id="nav-link-press">
                                    Prensa
                                </a>
                            </li>
                            <li>
                                <a href="#" id="nav-link-news">
                                    Noticias
                                </a>
                            </li>       
                        </ul>
                    </div>
        <!-- section: footer: 2nd column -->
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <h3 class="nav-title">COMUNIDADES</h3>
                        <ul class="nav">
                            <li>
                                <a href="#" id="nav-link-artists">
                                    Para Artistas
                                </a>
                            </li>

                            <li>
                                <a href="#" id="nav-link-developers">
                                    Desarrolladores    
                                </a>
                            </li>

                            <li>
                                <a href="#" id="nav-link-brands">
                                    Marcas    
                                </a>
                            </li>
                        </ul>
                    </div>
        <!-- section: footer: 3rd column -->
                    <div class="col-xs-6 col-sm-4 col-md-2">
                        <h3 class="nav-title">ENLACES ÚTILES</h3>
                        <ul class="nav">
                            <li>
                                <a href="#" id="nav-link-help">
                                    Ayuda
                                </a>
                            </li>
                            <li>
                                <a href="#" id="nav-link-gift">
                                    Regalos   
                                </a>
                            </li>
                            <li class="hidden-xs">
                                <a href="#" id="nav-link-play">
                                    Web Player    
                                </a>
                            </li>
                        </ul>
                    </div>
            <!-- section: footer: social -->
                    <div class="col-xs-12 col-md-4 col-social">
                        <ul class="nav">
                            <li>
                              <a href="#">
                                <img alt="instagram" src="https://cdn-icons-png.flaticon.com/512/87/87390.png"></a>
                            </li>
                            <li>
                              <a href="#">
                                <img alt="twitter" src="https://cdn-icons-png.flaticon.com/512/733/733635.png"></a>
                            </li>
                            <li>
                                <a href="#">
                                    <img alt="facebook" src="https://cdn-icons-png.flaticon.com/512/59/59439.png">
                                </a>
                            </li>
          
                        </ul>
                    </div>
                </nav>
            <!-- section: footer: small social -->
                    <nav class="row row-small">
                        <div class="col-xs-8 col-md-6">

                            <ul class="nav nav-small">
                                <li>
                                    <a href="#">Legal</a>
                                </li>
                                <li>
                                    <a href="#">Privacidad</a>
                                </li>
                                <li>
                                    <a href="#">Cookies</a>
                                </li>
                                <li>
                                    <a href="#">Acerca de los Anuncios</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-xs-4 col-md-6 text-right">
                        <a class="market" href="#" title="Click to change">
                            <div class="media">
                                <div class="media-body media-middle">
                                    SV
                                </div>
                                <div class="media-right media-middle">
                                  <span class="media-object flag-icon flag-icon-us"></span>
                                </div>
                            </div>
                        </a>

                        <small class="copyright">&copy; 2025 AURA</small>
                    </div>
                </nav>
            </div>
        </footer>

            <!-- jQuery library -->
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        </body>
    </html>