<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Google Translate oculto -->
    <div id="google_translate_element" style="display:none;"></div>

    <!-- Script oficial de Google -->
    <script type="text/javascript">
      function googleTranslateElementInit() {
        new google.translate.TranslateElement({
          pageLanguage: 'es',
          includedLanguages: 'es,en',
          autoDisplay: false
        }, 'google_translate_element');
      }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <!-- Script del botón -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const btn = document.getElementById("translateBtn");
        const label = document.getElementById("langLabel");
        if (!btn || !label) return;

        let currentLang = localStorage.getItem("lang") || "es";

        // Espera hasta que Google cargue el combo
        function waitForGoogleTranslate(callback) {
            const interval = setInterval(() => {
                const select = document.querySelector(".goog-te-combo");
                if (select) {
                    clearInterval(interval);
                    callback(select);
                }
            }, 500);
        }

        function setLanguage(lang) {
            waitForGoogleTranslate((select) => {
                select.value = lang;
                select.dispatchEvent(new Event("change"));
                label.textContent = (lang === "es") ? "EN" : "ES";
            });
        }

        // aplicar idioma guardado
        setLanguage(currentLang);

        // cambiar cuando le das click
        btn.addEventListener("click", function () {
            currentLang = (currentLang === "es") ? "en" : "es";
            localStorage.setItem("lang", currentLang);
            setLanguage(currentLang);
        });
    });
    </script>



<style type="text/css">
/* 🔹 Ocultar el banner de Google arriba */
.goog-te-banner-frame {
  display: none !important;
  height: 0 !important;
  visibility: hidden !important;
}

/* 🔹 Ocultar contenedor que Google mete */
.goog-te-banner {
  display: none !important;
}

/* 🔹 Ocultar tooltip */
#goog-gt-tt, .goog-tooltip, .goog-tooltip:hover {
  display: none !important;
  visibility: hidden !important;
}

/* 🔹 Evitar que el body quede desplazado */
body {
  top: 0px !important;
  position: static !important;
}
</style>


<script>
document.addEventListener("DOMContentLoaded", function () {
    function killGoogleBar() {
        // El iframe superior
        const banner = document.querySelector("iframe.goog-te-banner-frame");
        if (banner) banner.remove();

        // El contenedor de la barra
        const frameParent = document.querySelector(".goog-te-banner");
        if (frameParent) frameParent.remove();

        // El tooltip
        const tooltip = document.getElementById("goog-gt-tt");
        if (tooltip) tooltip.remove();

        // Resetear margen del body
        document.body.style.top = "0px";
    }

    // Ejecutar varias veces porque Google lo reinserta
    setInterval(killGoogleBar, 500);
});
</script>








</body>
</html>
