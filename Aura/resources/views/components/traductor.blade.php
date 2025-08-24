<!-- ===== BOTÓN FLOTANTE TRADUCTOR ===== -->
<div id="traductor-btn">
  <i class="fas fa-language"></i>
  <select id="lang">
    <option value="es">Español</option>
    <option value="en">Inglés</option>
  </select>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  /* ===== Estilo SOLO para el traductor ===== */
  #traductor-btn {
    position: fixed;
    bottom: 100px;
    right: 20px;
    background: #8a2be2;
    color: white;
    padding: 10px 15px;
    border-radius: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,.3);
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    z-index: 9999;
    transition: background .3s ease;
  }
  #traductor-btn:hover { background: #6a1bb9; }

  #traductor-btn select {
    background: #6a1bb9;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    padding: 6px 12px;
    outline: none;
    cursor: pointer;
    appearance: none;
    transition: background .3s ease, transform .2s ease;
  }
  #traductor-btn select:hover { background: #5a1499; transform: scale(1.05); }
  #traductor-btn select:focus { box-shadow: 0 0 0 2px rgba(255,255,255,0.6); }
  #traductor-btn select option { color: #333; background: #fff; }

  #traductor-btn i { color: #fff; font-size: 18px; }
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
let originalHTML = {};

function guardarOriginal() {
  let index = 0;
  let elementos = $("h1, h2, h3, p, span, a .label, .traducible");

  elementos.each(function() {
    let el = $(this);
    let t = el.text().trim();
    if (t.length > 0) {
      originalHTML[index] = {el: el, text: t, tipo: "texto"};
      index++;
    }
  });

  let placeholders = $("input[placeholder], textarea[placeholder]");
  placeholders.each(function() {
    let el = $(this);
    let ph = el.attr("placeholder");
    if (ph && ph.length > 0) {
      originalHTML[index] = {el: el, text: ph, tipo: "placeholder"};
      index++;
    }
  });
}

function restaurarOriginal() {
  Object.values(originalHTML).forEach(item => {
    if (item.tipo === "placeholder") {
      item.el.attr("placeholder", item.text);
    } else {
      item.el.text(item.text);
    }
  });
}

function traducirPagina(idioma) {
  if (idioma === "es") {
    restaurarOriginal();
    localStorage.setItem("idiomaSeleccionado", "es");
    return;
  }

  let textos = [];
  Object.values(originalHTML).forEach(item => textos.push(item.text));

  $.ajax({
    url: "{{ route('traducir') }}",
    method: "POST",
    data: { 
      textos: textos, 
      lang: idioma, 
      _token: "{{ csrf_token() }}" 
    },
    success: function(res) {
      let i = 0;
      Object.values(originalHTML).forEach(item => {
        if (item.tipo === "placeholder") {
          item.el.attr("placeholder", res[i]);
        } else {
          item.el.text(res[i]);
        }
        i++;
      });
      localStorage.setItem("idiomaSeleccionado", idioma);
    }
  });
}

$(document).ready(function() {
  guardarOriginal();

  // Leer idioma guardado (si existe) al entrar a la página
  let idiomaGuardado = localStorage.getItem("idiomaSeleccionado") || "es";
  $("#lang").val(idiomaGuardado);
  traducirPagina(idiomaGuardado);

  // Guardar idioma al cambiar
  $("#lang").change(function() {
    traducirPagina($(this).val());
  });
});
</script>