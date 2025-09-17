<!-- ===== BOTÓN FLOTANTE TRADUCTOR ===== -->
<div id="traductor-btn">
  <i class="fas fa-language"></i>
  <div class="custom-select">
    <select id="lang">
      <option value="es">🇪🇸 Español</option>
      <option value="en">🇺🇸 Inglés</option>
    </select>
    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
  </div>
</div>

<!-- CSRF token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  /* ===== Botón flotante ===== */
  #traductor-btn {
    position: fixed;
    bottom: 80px;
    right: 20px;
    background: linear-gradient(135deg, #7c3aed, #5a1499);
    color: #fff;
    padding: 10px 16px;
    border-radius: 35px;
    box-shadow: 0 6px 16px rgba(0,0,0,.35);
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    z-index: 9999;
    cursor: pointer;
    transition: all .25s ease-in-out;
  }
  #traductor-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,.45);
  }
  #traductor-btn i {
    font-size: 18px;
  }

  /* ===== Estilo del select ===== */
  .custom-select {
    position: relative;
    display: inline-block;
  }

  #lang {
    appearance: none;
    background: transparent;
    border: none;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    padding: 5px 25px 5px 5px;
    cursor: pointer;
    outline: none;
  }

  .custom-select .arrow {
    position: absolute;
    top: 50%;
    right: 5px;
    transform: translateY(-50%);
    pointer-events: none;
    color: #fff;
    font-size: 12px;
  }

  #lang option {
    background: #fff;
    color: #333;
    font-size: 14px;
  }
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
let originalHTML = {};
let cacheTraducciones = {};

function guardarOriginal() {
  let index = 0;
  $("body *:not(script):not(style)").contents().filter(function() {
    return this.nodeType === 3 && this.nodeValue.trim().length > 0;
  }).each(function() {
    let node = this;
    originalHTML[index] = { node: node, text: node.nodeValue, tipo: "texto" };
    index++;
  });
  $("input[placeholder], textarea[placeholder]").each(function() {
    let el = $(this);
    let ph = el.attr("placeholder");
    if (ph && ph.length > 0) {
      originalHTML[index] = { el: el, text: ph, tipo: "placeholder" };
      index++;
    }
  });
}

function restaurarOriginal() {
  Object.values(originalHTML).forEach(item => {
    if (item.tipo === "placeholder") {
      item.el.attr("placeholder", item.text);
    } else {
      item.node.nodeValue = item.text;
    }
  });
}

function dividirEnChunks(array, size) {
  const result = [];
  for (let i = 0; i < array.length; i += size) {
    result.push(array.slice(i, i + size));
  }
  return result;
}

function traducirPagina(idioma) {
  if (idioma === "es") {
    restaurarOriginal();
    localStorage.setItem("idiomaSeleccionado", "es");
    return;
  }

  let textos = Object.values(originalHTML).map(item => item.text);
  let textosUnicos = [...new Set(textos.filter(t => t.trim().length > 0))];
  textosUnicos = textosUnicos.filter(t => !cacheTraducciones[t]);

  if (textosUnicos.length === 0) {
    aplicarTraducciones();
    return;
  }

  let chunks = dividirEnChunks(textosUnicos, 30);
  let promesas = chunks.map(chunk =>
    $.ajax({
      url: "/traducir",
      method: "POST",
      headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
      data: { textos: chunk, lang: idioma }
    }).then(res => {
      for (let i = 0; i < chunk.length; i++) {
        cacheTraducciones[chunk[i]] = res[i];
      }
    })
  );

  Promise.all(promesas).then(() => {
    aplicarTraducciones();
    localStorage.setItem("idiomaSeleccionado", idioma);
  });
}

function aplicarTraducciones() {
  Object.values(originalHTML).forEach(item => {
    let txt = item.text;
    let traducido = cacheTraducciones[txt] || txt;
    if (item.tipo === "placeholder") {
      item.el.attr("placeholder", traducido);
    } else {
      item.node.nodeValue = traducido;
    }
  });
}

$(document).ready(function() {
  guardarOriginal();

  // 📝 Mantener idioma al navegar
  let idiomaGuardado = localStorage.getItem("idiomaSeleccionado") || "es";
  $("#lang").val(idiomaGuardado);
  traducirPagina(idiomaGuardado);

  $("#lang").change(function() {
    traducirPagina($(this).val());
  });
});
</script>
